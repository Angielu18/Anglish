<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Core\Response;
use App\Models\AuditLog;
use App\Models\Dashboard;

/**
 * Construye el tablero correspondiente al rol autenticado.
 */
final class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $user = Auth::user();
        $role = (string) Auth::role();
        $model = new Dashboard();

        if ($role === 'ADMINISTRADOR') {
            $data = $model->admin();
            $title = 'Panel administrativo';
            $subtitle = 'Visión general de la academia';
        } elseif ($role === 'PROFESOR') {
            $profesorId = Auth::profesorId();

            if ($profesorId === null) {
                Response::abort(403, 'Tu usuario no tiene un perfil de profesor asociado.');
            }

            $data = $model->professor($profesorId);
            $title = 'Panel del profesor';
            $subtitle = 'Tus cursos, sesiones y estudiantes';
        } elseif ($role === 'ESTUDIANTE') {
            $estudianteId = Auth::estudianteId();

            if ($estudianteId === null) {
                Response::abort(403, 'Tu usuario no tiene un perfil de estudiante asociado.');
            }

            $data = $model->student($estudianteId);
            $title = 'Mi panel';
            $subtitle = 'Tu proceso de aprendizaje de un vistazo';
        } else {
            Response::abort(403, 'Rol no reconocido.');
        }

        $this->render('dashboard/index', [
            'title'       => $title,
            'subtitle'    => $subtitle,
            'user'        => $user,
            'role'        => $role,
            'dashboard'   => $data,
            'currentDate' => fecha_hoy_larga(),
        ]);
    }

    public function markAlertsRead(): void
    {
        $usuarioId = Auth::id();

        if ($usuarioId === null) {
            Response::abort(403);
        }

        $cantidad = (new Dashboard())->markAlertsRead($usuarioId);

        (new AuditLog())->record(
            $usuarioId,
            'LEER_ALERTAS',
            'alertas',
            'Alertas marcadas como leídas: ' . $cantidad
        );

        Flash::set(
            'success',
            $cantidad > 0
                ? 'Se marcaron ' . $cantidad . ' alerta' . ($cantidad === 1 ? '' : 's') . ' como leída' . ($cantidad === 1 ? '' : 's') . '.'
                : 'No tenías alertas pendientes.'
        );

        Response::redirect('dashboard');
    }
}
