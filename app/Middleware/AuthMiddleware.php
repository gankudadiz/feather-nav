<?php

declare(strict_types=1);

namespace App\Middleware;

use Flight;

class AuthMiddleware
{
    public function before(): void
    {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            Flight::redirect('/auth/login');
            exit;
        }
    }
}
