<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Models\AuditLog;
use App\Models\Tarea;

/**
 * Controlador del Módulo de Estudiantes.
 *
 * Secciones:
 *  - Entregar trabajos y ver notas
 *  - Reportes de progreso y asistencia
 */
final class EstudianteController extends Controller
{
    public function trabajos(): void
    {
        Auth::requireRole('ESTUDIANTE');

        $estudianteId = Auth::estudianteId();
        if ($estudianteId === null) {
            Response::abort(403, 'No tienes perfil de estudiante activo.');
        }

        $model = new Tarea();
        $trabajos = $model->listarPorEstudiante($estudianteId);

        $this->render('estudiante/trabajos', [
            'title'       => 'Entregar trabajos y ver notas',
            'subtitle'    => 'Gestiona tus entregas académicas y consulta tu retroalimentación',
            'user'        => Auth::user(),
            'role'        => 'ESTUDIANTE',
            'trabajos'    => $trabajos,
            'currentDate' => fecha_hoy_larga(),
        ]);
    }

    public function entregar(): void
    {
        Auth::requireRole('ESTUDIANTE');

        $evaluacionId  = (int) Request::post('evaluacion_id', 0);
        $matriculaId   = (int) Request::post('matricula_id', 0);
        $archivoUrl    = trim((string) Request::post('archivo_url', ''));
        $archivoNombre = trim((string) Request::post('archivo_nombre', ''));
        $comentario    = trim((string) Request::post('comentario_estudiante', ''));

        if ($evaluacionId <= 0 || $matriculaId <= 0) {
            Flash::set('danger', 'Selecciona una evaluación válida para entregar.');
            Response::redirect('estudiante/trabajos');
        }

        if ($archivoNombre === '' && $archivoUrl !== '') {
            $archivoNombre = basename($archivoUrl);
        } elseif ($archivoNombre === '') {
            $archivoNombre = 'entrega_estudiante.pdf';
        }

        if ($archivoUrl === '') {
            $archivoUrl = 'uploads/tareas/' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $archivoNombre);
        }

        $model = new Tarea();
        $model->guardarEntrega($evaluacionId, $matriculaId, $archivoUrl, $archivoNombre, $comentario);

        (new AuditLog())->record(
            Auth::id(),
            'ENTREGAR_TAREA',
            'Estudiantes',
            'Entrega registrada para evaluación #' . $evaluacionId
        );

        Flash::set('success', 'Tu trabajo ha sido enviado correctamente al docente para su revisión.');
        Response::redirect('estudiante/trabajos');
    }

    public function reportes(): void
    {
        Auth::requireRole('ESTUDIANTE');

        $estudianteId = Auth::estudianteId();
        if ($estudianteId === null) {
            Response::abort(403, 'No tienes perfil de estudiante activo.');
        }

        $model = new Tarea();
        $reporte = $model->obtenerReporteEstudiante($estudianteId);

        $this->render('estudiante/reportes', [
            'title'       => 'Mis Reportes Académicos',
            'subtitle'    => 'Seguimiento detallado de progreso, notas y asistencias',
            'user'        => Auth::user(),
            'role'        => 'ESTUDIANTE',
            'reporte'     => $reporte,
            'currentDate' => fecha_hoy_larga(),
        ]);
    }
}
