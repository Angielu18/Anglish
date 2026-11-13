<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Models\AuditLog;
use App\Models\Dashboard;
use App\Models\Tarea;

/**
 * Controlador del Módulo de Profesores.
 *
 * Secciones:
 *  - Revisar trabajos (calificar y retroalimentar entregas)
 *  - Gestionar tareas (crear y asignar por curso/nivel)
 *  - Reportes (rendimiento grupal y estudiantes en riesgo)
 */
final class ProfesorController extends Controller
{
    public function revisar(): void
    {
        Auth::requireRole('PROFESOR');

        $profesorId = Auth::profesorId();
        if ($profesorId === null) {
            Response::abort(403, 'Tu usuario no tiene perfil de profesor asociado.');
        }

        $model = new Tarea();
        $entregas = $model->listarEntregasParaProfesor($profesorId);

        $this->render('profesor/revisar', [
            'title'       => 'Revisar trabajos de estudiantes',
            'subtitle'    => 'Evalúa, califica y envía retroalimentación formativa',
            'user'        => Auth::user(),
            'role'        => 'PROFESOR',
            'entregas'    => $entregas,
            'currentDate' => fecha_hoy_larga(),
        ]);
    }

    public function calificar(): void
    {
        Auth::requireRole('PROFESOR');

        $entregaId = (int) Request::post('entrega_id', 0);
        $nota      = (float) str_replace(',', '.', (string) Request::post('nota', '0'));
        $retro     = trim((string) Request::post('retroalimentacion', ''));

        if ($entregaId <= 0 || $nota < 1.0 || $nota > 5.0) {
            Flash::set('danger', 'La calificación debe estar entre 1.0 y 5.0.');
            Response::redirect('profesor/revisar');
        }

        $model = new Tarea();
        $ok = $model->calificarEntrega($entregaId, round($nota, 1), $retro, (int) Auth::id());

        if ($ok) {
            (new AuditLog())->record(
                Auth::id(),
                'CALIFICAR_ENTREGA',
                'Profesores',
                'Entrega #' . $entregaId . ' calificada con nota ' . $nota
            );
            Flash::set('success', 'Calificación y retroalimentación guardadas exitosamente.');
        } else {
            Flash::set('danger', 'No se encontró la entrega seleccionada.');
        }

        Response::redirect('profesor/revisar');
    }

    public function tareas(): void
    {
        Auth::requireRole('PROFESOR');

        $profesorId = Auth::profesorId();
        if ($profesorId === null) {
            Response::abort(403, 'Tu usuario no tiene perfil de profesor asociado.');
        }

        $tareaModel = new Tarea();
        $dashModel  = new Dashboard();
        $profData   = $dashModel->professor($profesorId);

        $this->render('profesor/tareas', [
            'title'       => 'Gestionar tareas y evaluaciones',
            'subtitle'    => 'Crea, programa y asigna actividades académicas por curso y nivel',
            'user'        => Auth::user(),
            'role'        => 'PROFESOR',
            'tareas'      => $tareaModel->listarPorProfesor($profesorId),
            'cursos'      => $profData['cursos'] ?? [],
            'currentDate' => fecha_hoy_larga(),
        ]);
    }

    public function crearTarea(): void
    {
        Auth::requireRole('PROFESOR');

        $cursoId      = (int) Request::post('curso_id', 0);
        $nombre       = trim((string) Request::post('nombre', ''));
        $tipo         = strtoupper(trim((string) Request::post('tipo', 'TALLER')));
        $porcentaje   = (float) str_replace(',', '.', (string) Request::post('porcentaje', '10'));
        $fechaEntrega = trim((string) Request::post('fecha_entrega', ''));
        $descripcion  = trim((string) Request::post('descripcion', ''));

        $tiposValidos = ['TALLER', 'QUIZ', 'PARCIAL', 'PROYECTO', 'EXAMEN_FINAL'];
        if (!in_array($tipo, $tiposValidos, true)) {
            $tipo = 'TALLER';
        }

        if ($cursoId <= 0 || $nombre === '' || $porcentaje <= 0 || $porcentaje > 100 || $fechaEntrega === '') {
            Flash::set('danger', 'Completa todos los campos obligatorios con valores válidos.');
            Response::redirect('profesor/tareas');
        }

        // Formatear fecha_entrega a DATETIME si viene de input datetime-local
        $fechaFormato = str_replace('T', ' ', $fechaEntrega);
        if (strlen($fechaFormato) === 16) {
            $fechaFormato .= ':00';
        }

        try {
            $model = new Tarea();
            $id = $model->crearTarea($cursoId, $nombre, $tipo, $porcentaje, $fechaFormato, $descripcion, (int) Auth::id());

            (new AuditLog())->record(
                Auth::id(),
                'CREAR_TAREA',
                'Profesores',
                'Tarea creada #' . $id . ': ' . $nombre
            );

            Flash::set('success', 'La tarea "' . $nombre . '" fue asignada exitosamente al curso.');
        } catch (\Throwable $e) {
            Flash::set('danger', 'No se pudo crear la tarea. Verifica que no exista otra con el mismo nombre en ese curso.');
        }

        Response::redirect('profesor/tareas');
    }

    public function reportes(): void
    {
        Auth::requireRole('PROFESOR');

        $profesorId = Auth::profesorId();
        if ($profesorId === null) {
            Response::abort(403, 'Tu usuario no tiene perfil de profesor asociado.');
        }

        $model = new Tarea();
        $reporte = $model->obtenerReporteProfesor($profesorId);

        $this->render('profesor/reportes', [
            'title'       => 'Reportes de rendimiento docente',
            'subtitle'    => 'Estadísticas por grupo, promedios y detección temprana de riesgo académico',
            'user'        => Auth::user(),
            'role'        => 'PROFESOR',
            'reporte'     => $reporte,
            'currentDate' => fecha_hoy_larga(),
        ]);
    }
}
