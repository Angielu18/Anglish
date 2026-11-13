<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Models\AuditLog;
use App\Models\Entrevista;
use App\Models\User;

/**
 * Controlador del Módulo de Administrador.
 *
 * Secciones:
 *  - Gestionar entrevistas (programar, coordinar y evaluar)
 *  - Gestión de acceso al sistema (usuarios, roles y permisos)
 */
final class AdminController extends Controller
{
    public function entrevistas(): void
    {
        Auth::requireRole('ADMINISTRADOR');

        $filtroEstado = isset($_GET['estado']) && is_string($_GET['estado']) ? trim($_GET['estado']) : null;

        $model = new Entrevista();

        $this->render('admin/entrevistas', [
            'title'        => 'Gestionar entrevistas',
            'subtitle'     => 'Programación, coordinación y seguimiento de entrevistas de admisión y nivelación',
            'user'         => Auth::user(),
            'role'         => 'ADMINISTRADOR',
            'entrevistas'  => $model->listarTodas($filtroEstado),
            'niveles'      => $model->listarNiveles(),
            'docentes'     => $model->listarDocentes(),
            'filtroEstado' => $filtroEstado,
            'currentDate'  => fecha_hoy_larga(),
        ]);
    }

    public function crearEntrevista(): void
    {
        Auth::requireRole('ADMINISTRADOR');

        $nombre         = trim((string) Request::post('candidato_nombre', ''));
        $email          = trim((string) Request::post('candidato_email', ''));
        $telefono       = trim((string) Request::post('candidato_telefono', ''));
        $nivelId        = (int) Request::post('nivel_interes_id', 1);
        $fechaHora      = trim((string) Request::post('fecha_hora', ''));
        $modalidad      = strtoupper(trim((string) Request::post('modalidad', 'VIRTUAL')));
        $enlaceLugar    = trim((string) Request::post('enlace_o_lugar', ''));
        $entrevistadorId = (int) Request::post('entrevistador_id', 0);
        $observaciones  = trim((string) Request::post('observaciones', ''));

        if ($nombre === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $telefono === '' || $fechaHora === '') {
            Flash::set('danger', 'Por favor completa el nombre, correo válido, teléfono y fecha/hora de la entrevista.');
            Response::redirect('admin/entrevistas');
        }

        if (!in_array($modalidad, ['VIRTUAL', 'PRESENCIAL'], true)) {
            $modalidad = 'VIRTUAL';
        }

        $fechaFormato = str_replace('T', ' ', $fechaHora);
        if (strlen($fechaFormato) === 16) {
            $fechaFormato .= ':00';
        }

        $model = new Entrevista();
        $id = $model->crear(
            $nombre,
            $email,
            $telefono,
            $nivelId > 0 ? $nivelId : null,
            $fechaFormato,
            $modalidad,
            $enlaceLugar !== '' ? $enlaceLugar : ($modalidad === 'VIRTUAL' ? 'meet.google.com/ang-interview' : 'Sede Principal'),
            $entrevistadorId > 0 ? $entrevistadorId : null,
            $observaciones
        );

        (new AuditLog())->record(
            Auth::id(),
            'CREAR_ENTREVISTA',
            'Entrevistas',
            'Entrevista #' . $id . ' programada para ' . $nombre
        );

        Flash::set('success', 'Entrevista programada exitosamente para ' . $nombre . '.');
        Response::redirect('admin/entrevistas');
    }

    public function actualizarEntrevista(): void
    {
        Auth::requireRole('ADMINISTRADOR');

        $id             = (int) Request::post('entrevista_id', 0);
        $estado         = strtoupper(trim((string) Request::post('estado', 'PROGRAMADA')));
        $resultadoNivel = trim((string) Request::post('resultado_nivel', ''));
        $observaciones  = trim((string) Request::post('observaciones', ''));

        $estadosValidos = ['PROGRAMADA', 'REALIZADA', 'CANCELADA', 'APROBADA', 'NO_ASISTIO'];
        if ($id <= 0 || !in_array($estado, $estadosValidos, true)) {
            Flash::set('danger', 'Datos de entrevista inválidos.');
            Response::redirect('admin/entrevistas');
        }

        $model = new Entrevista();
        $model->actualizarEstado(
            $id,
            $estado,
            $resultadoNivel !== '' ? strtoupper($resultadoNivel) : null,
            $observaciones !== '' ? $observaciones : null
        );

        (new AuditLog())->record(
            Auth::id(),
            'ACTUALIZAR_ENTREVISTA',
            'Entrevistas',
            'Entrevista #' . $id . ' actualizada a estado ' . $estado
        );

        Flash::set('success', 'Estado y seguimiento de la entrevista actualizados.');
        Response::redirect('admin/entrevistas');
    }

    public function usuarios(): void
    {
        Auth::requireRole('ADMINISTRADOR');

        $filtroRol    = isset($_GET['rol']) && is_string($_GET['rol']) ? trim($_GET['rol']) : null;
        $filtroEstado = isset($_GET['estado']) && is_string($_GET['estado']) ? trim($_GET['estado']) : null;

        $userModel = new User();

        $this->render('admin/usuarios', [
            'title'        => 'Gestión de acceso al sistema',
            'subtitle'     => 'Administración de usuarios, roles (Administrador, Profesor, Estudiante) y permisos',
            'user'         => Auth::user(),
            'role'         => 'ADMINISTRADOR',
            'usuarios'     => $userModel->listarTodos($filtroRol, $filtroEstado),
            'roles'        => $userModel->listarRoles(),
            'permisos'     => $userModel->listarPermisosPorRol(),
            'filtroRol'    => $filtroRol,
            'filtroEstado' => $filtroEstado,
            'currentDate'  => fecha_hoy_larga(),
        ]);
    }

    public function crearUsuario(): void
    {
        Auth::requireRole('ADMINISTRADOR');

        $rolId     = (int) Request::post('rol_id', 3);
        $nombres   = trim((string) Request::post('nombres', ''));
        $apellidos = trim((string) Request::post('apellidos', ''));
        $documento = trim((string) Request::post('documento', ''));
        $email     = trim((string) Request::post('email', ''));
        $telefono  = trim((string) Request::post('telefono', ''));
        $password  = (string) Request::post('password', '');

        if ($nombres === '' || $apellidos === '' || $documento === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            Flash::set('danger', 'Completa todos los datos obligatorios. La contraseña debe tener mínimo 8 caracteres.');
            Response::redirect('admin/usuarios');
        }

        if (!in_array($rolId, [1, 2, 3], true)) {
            $rolId = 3;
        }

        try {
            $userModel = new User();
            $nuevoId = $userModel->crearUsuario(
                $rolId,
                $nombres,
                $apellidos,
                $documento,
                $email,
                $password,
                $telefono !== '' ? $telefono : null
            );

            (new AuditLog())->record(
                Auth::id(),
                'CREAR_USUARIO',
                'Seguridad',
                'Usuario #' . $nuevoId . ' creado con correo ' . $email
            );

            Flash::set('success', 'Usuario ' . $nombres . ' ' . $apellidos . ' registrado exitosamente.');
        } catch (\Throwable $e) {
            Flash::set('danger', 'No se pudo registrar el usuario. Verifica que el correo o documento no estén duplicados.');
        }

        Response::redirect('admin/usuarios');
    }

    public function actualizarUsuario(): void
    {
        Auth::requireRole('ADMINISTRADOR');

        $usuarioId = (int) Request::post('usuario_id', 0);
        $rolId     = (int) Request::post('rol_id', 3);
        $estado    = strtoupper(trim((string) Request::post('estado', 'ACTIVO')));

        if ($usuarioId <= 0 || !in_array($rolId, [1, 2, 3], true) || !in_array($estado, ['ACTIVO', 'INACTIVO', 'BLOQUEADO'], true)) {
            Flash::set('danger', 'Parámetros de actualización inválidos.');
            Response::redirect('admin/usuarios');
        }

        // Evitar que el administrador activo se desactive a sí mismo
        if ($usuarioId === (int) Auth::id() && $estado !== 'ACTIVO') {
            Flash::set('warning', 'No puedes desactivar o bloquear tu propia cuenta activa.');
            Response::redirect('admin/usuarios');
        }

        $userModel = new User();
        $userModel->actualizarAcceso($usuarioId, $rolId, $estado);

        (new AuditLog())->record(
            Auth::id(),
            'ACTUALIZAR_ACCESO',
            'Seguridad',
            'Usuario #' . $usuarioId . ' actualizado a rol #' . $rolId . ' y estado ' . $estado
        );

        Flash::set('success', 'Permisos, rol y estado del usuario actualizados correctamente.');
        Response::redirect('admin/usuarios');
    }
}
