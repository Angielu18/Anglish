<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Response;
use App\Models\Dashboard;
use App\Models\User;

final class ApiController extends Controller
{
    private User $userModel;
    private Dashboard $dashboardModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->dashboardModel = new Dashboard();
    }

    public function login(): void
    {
        $input = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = $_POST;
        }

        $email = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');

        if ($email === '' || $password === '') {
            Response::json(['error' => 'Email y contraseña requeridos'], 400);
        }

        $user = $this->userModel->findForAuth($email);
        if ($user === null || !password_verify($password, (string) $user['password_hash'])) {
            Response::json(['error' => 'Credenciales inválidas'], 401);
        }

        if (($user['estado'] ?? '') !== 'ACTIVO') {
            Response::json(['error' => 'Usuario inactivo'], 403);
        }

        $token = 'Bearer anglish-token-' . base64_encode($email . ':' . time());

        Response::json([
            'token' => $token,
            'usuario' => [
                'id' => (int) $user['id'],
                'nombres' => $user['nombres'],
                'apellidos' => $user['apellidos'],
                'email' => $user['email'],
                'rol' => $user['rol'],
                'nivel' => $user['nivel_codigo'] ?? 'A1',
            ],
        ]);
    }

    public function studentDashboard(): void
    {
        $estudianteId = (int) ($_GET['id'] ?? 1);
        $data = $this->dashboardModel->student($estudianteId);

        $matricula = $data['matricula'] ?? [];
        $progreso = $data['progreso'] ?? [];
        $asistencias = $data['asistencias'] ?? [];
        $evaluaciones = $data['evaluaciones'] ?? [];

        $cursoId = (int) ($matricula['curso_id'] ?? 1);
        $matriculaId = (int) ($matricula['matricula_id'] ?? 1);

        $evaluacionesDto = [];
        foreach ($evaluaciones as $eval) {
            $evaluacionesDto[] = [
                'id' => (int) $eval['id'],
                'curso_id' => $cursoId,
                'curso_nombre' => $matricula['curso'] ?? 'Inglés',
                'titulo' => $eval['nombre'],
                'tipo' => $eval['tipo'],
                'fecha_entrega' => $eval['fecha_entrega'],
                'porcentaje' => (float) $eval['porcentaje'],
                'nota' => isset($eval['nota']) && $eval['nota'] !== null ? (float) $eval['nota'] : null,
                'leida' => false,
                'completada' => isset($eval['nota']) && $eval['nota'] !== null,
            ];
        }

        $asistenciasDto = [];
        foreach ($asistencias as $asist) {
            $asistenciasDto[] = [
                'id' => (int) $asist['id'],
                'matricula_id' => $matriculaId,
                'curso_nombre' => $matricula['curso'] ?? 'Inglés',
                'fecha' => $asist['fecha'],
                'estado' => $asist['estado'],
                'tema' => $asist['tema'] ?? 'Clase regular',
            ];
        }

        Response::json([
            'estudiante' => [
                'id' => $estudianteId,
                'nombres' => $data['estudiante']['nombres'] ?? 'Sara',
                'apellidos' => $data['estudiante']['apellidos'] ?? 'Gómez',
                'email' => $data['estudiante']['email'] ?? 'sara.gomez@anglish.edu.co',
                'rol' => 'ESTUDIANTE',
                'nivel' => $matricula['nivel_codigo'] ?? 'A1',
            ],
            'curso' => [
                'id' => $cursoId,
                'codigo' => $matricula['codigo'] ?? 'ING-A1-01',
                'nombre' => $matricula['curso'] ?? 'Inglés A1 — Fundamentos',
                'nivel' => $matricula['nivel_codigo'] ?? 'A1',
                'profesor' => $matricula['profesor'] ?? 'Laura Méndez',
                'aula' => $matricula['aula'] ?? 'Aula 201',
                'horario' => $matricula['jornada'] ?? 'Mañana',
            ],
            'matricula' => [
                'id' => $matriculaId,
                'curso_id' => $cursoId,
                'progreso_porcentaje' => (float) ($progreso['porcentaje_avance'] ?? 45.0),
                'promedio' => (float) ($progreso['promedio_ponderado'] ?? 2.53),
            ],
            'evaluaciones' => $evaluacionesDto,
            'asistencias' => $asistenciasDto,
        ]);
    }
}
