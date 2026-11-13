<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Flash;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\AuditLog;

/**
 * Inicio y cierre de sesión.
 */
final class AuthController extends Controller
{
    public function showLogin(): void
    {
        $oldEmail = (string) ($_SESSION['_old_email'] ?? '');
        unset($_SESSION['_old_email']);

        $this->render('auth/login', [
            'title'    => 'Iniciar sesión',
            'oldEmail' => $oldEmail,
        ], 'auth');
    }

    public function login(): void
    {
        $email = trim((string) Request::post('email', ''));
        $password = (string) Request::post('password', '');

        $errors = Validator::validate(
            ['email' => $email, 'password' => $password],
            [
                'email'    => 'required|email|max:120',
                'password' => 'required|min:8|max:72',
            ],
            [
                'email'    => 'correo electrónico',
                'password' => 'contraseña',
            ]
        );

        if ($errors !== []) {
            Flash::set('danger', (string) reset($errors));
            $_SESSION['_old_email'] = $email;
            Response::redirect('login');
        }

        $resultado = Auth::attempt($email, $password);

        if (!$resultado['ok']) {
            Flash::set('danger', $resultado['mensaje']);
            $_SESSION['_old_email'] = $email;
            Response::redirect('login');
        }

        $user = Auth::user();

        (new AuditLog())->record(
            Auth::id(),
            'LOGIN',
            'auth',
            'Inicio de sesión de ' . ($user['email'] ?? $email)
        );

        Flash::set('success', 'Bienvenido a Anglish, ' . ($user['nombres'] ?? ''));
        Response::redirect(Auth::homeForRole((string) Auth::role()));
    }

    public function logout(): void
    {
        $user = Auth::user();

        if ($user !== null) {
            (new AuditLog())->record(
                (int) $user['id'],
                'LOGOUT',
                'auth',
                'Cierre de sesión de ' . (string) $user['email']
            );
        }

        Auth::logout();
        Flash::set('success', 'Tu sesión se cerró correctamente.');
        Response::redirect('login');
    }
}
