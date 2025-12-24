<?php

declare(strict_types=1);

namespace Modules\User\Presentation\Controller;

use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Controller\BaseController;

/**
 * Auth Controller
 * 
 * Handles authentication (login/logout)
 */
class AuthController extends BaseController
{
    public function __construct(
        AuthService $authService,
        KeyValidator $keyValidator,
        Request $request,
        Response $response
    ) {
        parent::__construct($authService, $keyValidator, $request, $response);
    }

    /**
     * Handle login
     */
    public function login(): void
    {
        if ($this->request->method() === 'GET') {
            // Show login form
            if ($this->isAuthenticated()) {
                $this->redirect('/admin/dashboard');
                return;
            }
            
            $this->render('admin/login');
            return;
        }

        // Handle POST login
        if ($this->request->method() === 'POST') {
            $username = $this->request->input('username');
            $password = $this->request->input('password');

            if (empty($username) || empty($password)) {
                $this->render('admin/login', [
                    'error' => 'Vui lòng nhập tên đăng nhập và mật khẩu'
                ]);
                return;
            }

            // Authenticate user (username is actually email)
            $user = $this->authService->authenticate($username, $password);

            if ($user) {
                // Set session
                $this->authService->login($user);
                $this->redirect('/admin/dashboard');
            } else {
                $this->render('admin/login', [
                    'error' => 'Tên đăng nhập hoặc mật khẩu không đúng'
                ]);
            }
        }
    }

    /**
     * Handle logout
     */
    public function logout(): void
    {
        $this->authService->logout();
        $this->redirect('/admin/login');
    }
}
