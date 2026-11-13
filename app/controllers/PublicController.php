<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Models\Entrevista;

/**
 * Controlador del Módulo Público / Visitantes.
 *
 * Permite que cualquier persona no registrada consulte la información
 * institucional ("¿Quiénes somos?", programas A1-C1, objetivos y metodología)
 * sin tener acceso a los servicios internos de estudiantes o profesores.
 */
final class PublicController extends Controller
{
    public function index(): void
    {
        $entrevistaModel = new Entrevista();
        $niveles = $entrevistaModel->listarNiveles();

        $this->render('public/landing', [
            'title'          => 'Inicio — Academia de inglés',
            'niveles'        => $niveles,
            'isLogged'       => Auth::check(),
            'user'           => Auth::user(),
        ], 'public');
    }

    public function solicitarEntrevista(): void
    {
        $nombre   = trim((string) Request::post('nombre', ''));
        $email    = trim((string) Request::post('email', ''));
        $telefono = trim((string) Request::post('telefono', ''));
        $nivelId  = (int) Request::post('nivel_id', 1);
        $modalidad = strtoupper(trim((string) Request::post('modalidad', 'VIRTUAL')));
        $fecha    = trim((string) Request::post('fecha_preferida', ''));
        $obs      = trim((string) Request::post('observaciones', ''));

        if ($nombre === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $telefono === '') {
            Flash::set('danger', 'Por favor completa tu nombre, un correo válido y tu teléfono de contacto.');
            Response::redirect('landing');
        }

        if (!in_array($modalidad, ['VIRTUAL', 'PRESENCIAL'], true)) {
            $modalidad = 'VIRTUAL';
        }

        $fechaHora = $fecha !== '' ? $fecha . ' 10:00:00' : date('Y-m-d 10:00:00', strtotime('+2 days'));

        $model = new Entrevista();
        $model->crear(
            $nombre,
            $email,
            $telefono,
            $nivelId > 0 ? $nivelId : 1,
            $fechaHora,
            $modalidad,
            $modalidad === 'VIRTUAL' ? 'Enlace por confirmar vía correo' : 'Sede Principal Anglish',
            null,
            $obs !== '' ? $obs : 'Solicitud registrada desde el portal público de visitantes.'
        );

        Flash::set('success', '¡Tu solicitud de entrevista diagnóstica ha sido recibida! Nuestro equipo académico te contactará pronto.');
        Response::redirect('landing');
    }
}
