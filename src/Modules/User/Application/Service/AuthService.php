<?php

declare(strict_types=1);

namespace Modules\User\Application\Service;

use Modules\User\Domain\Model\User;
use Modules\User\Infrastructure\Repository\UserRepository;
use Modules\Authorization\Application\Service\AuthorizationService;
use Shared\Infrastructure\Exception\UnauthorizedException;
use Shared\Infrastructure\Exception\ForbiddenException;

class AuthService
{
    // Session timeout in seconds (default: 30 minutes)
    private const SESSION_TIMEOUT = 1800;
    
    private UserRepository $userRepository;
    private ?AuthorizationService $authorizationService = null;
    private ?\Modules\Security\Application\Service\SecurityService $securityService = null;

    public function __construct(
        UserRepository $userRepository,
        ?AuthorizationService $authorizationService = null,
        ?\Modules\Security\Application\Service\SecurityService $securityService = null
    ) {
        $this->userRepository = $userRepository;
        $this->authorizationService = $authorizationService;
        $this->securityService = $securityService;
    }

    /**
     * Authenticate user by email and password
     */
    public function authenticate(string $email, string $password): ?User
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if ($this->securityService) {
            // Check if IP is blocked
            if ($this->securityService->isIpBlocked($ip)) {
                throw new ForbiddenException('Access denied. Your IP is blocked.');
            }

            // Check login throttling (5 attempts in 15 minutes)
            $failures = $this->securityService->countRecentAuthFailures($ip, 15);
            if ($failures >= 5) {
                // Log lockout event (if not already logged?)
                // Actually if we just throw, we don't increment failure again?
                // The failures are from PAST logs.
                throw new \RuntimeException('Too many login attempts. Please try again in 15 minutes.');
            }
        }

        $user = $this->userRepository->findByEmail($email);
        
        $loginFailed = function($userId = null) use ($ip, $email) {
            if ($this->securityService) {
                $this->securityService->log(
                    'auth.login_fail',
                    $ip,
                    "Login failed for email: $email",
                    $userId,
                    $_SERVER['HTTP_USER_AGENT'] ?? null
                );
            }
        };

        if ($user === null) {
            $loginFailed();
            return null;
        }

        if (!$user->isActive()) {
            $loginFailed($user->getId());
            // Log specifically for inactive?
            return null;
        }

        if (!password_verify($password, $user->getPasswordHash())) {
            $loginFailed($user->getId());
            return null;
        }

        // Authentication successful
        if ($this->securityService) {
            $this->securityService->log(
                'auth.login_success',
                $ip,
                "User logged in",
                $user->getId(),
                $_SERVER['HTTP_USER_AGENT'] ?? null
            );
        }

        // Update last login
        $user->setLastLoginAt(new \DateTimeImmutable());
        $this->userRepository->save($user);

        return $user;
    }

    /**
     * Create new user
     */
    public function createUser(
        string $email,
        string $password,
        string $fullName,
        string $role = 'admin'
    ): User {
        // Check if user exists
        $existing = $this->userRepository->findByEmail($email);
        if ($existing !== null) {
            throw new \RuntimeException('User with this email already exists.');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $user = new User($email, $passwordHash, $fullName, $role);
        
        $this->userRepository->save($user);

        return $user;
    }

    /**
     * Change user password
     */
    public function changePassword(User $user, string $newPassword): void
    {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $user->setPasswordHash($passwordHash);
        $this->userRepository->save($user);

        if ($this->securityService) {
            $this->securityService->log(
                'user.password_change',
                $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                "Password changed for user ID: {$user->getId()}",
                $user->getId(),
                $_SERVER['HTTP_USER_AGENT'] ?? null
            );
        }
    }

    /**
     * Get current authenticated user from session
     */
    public function getCurrentUser(): ?User
    {
        $userId = \Shared\Infrastructure\Session\SessionManager::get('user_id');
        if ($userId === null) {
            return null;
        }
        
        // Check session timeout
        if ($this->isSessionExpired()) {
            $this->logout();
            return null;
        }
        
        // Update last activity time
        $this->updateLastActivity();

        return $this->userRepository->findById((int)$userId);
    }

    /**
     * Login user (set session)
     */
    public function login(User $user): void
    {
        \Shared\Infrastructure\Session\SessionManager::regenerate();
        \Shared\Infrastructure\Session\SessionManager::set('user_id', $user->getId());
        \Shared\Infrastructure\Session\SessionManager::set('user_email', $user->getEmail());
        \Shared\Infrastructure\Session\SessionManager::set('user_role', $user->getRole());
        \Shared\Infrastructure\Session\SessionManager::set('last_activity', time());
        \Shared\Infrastructure\Session\SessionManager::set('login_time', time());
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        \Shared\Infrastructure\Session\SessionManager::clear();
        \Shared\Infrastructure\Session\SessionManager::destroy();
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return $this->getCurrentUser() !== null;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        $user = $this->getCurrentUser();
        return $user !== null && $user->isAdmin();
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        if ($this->authorizationService === null) {
            return false;
        }
        
        $user = $this->getCurrentUser();
        if ($user === null) {
            return false;
        }
        
        return $this->authorizationService->userHasRole($user, 'super_admin');
    }

    /**
     * Require authentication (throw exception if not authenticated)
     */
    public function requireAuth(): User
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            throw new \RuntimeException('Authentication required.');
        }
        return $user;
    }

    /**
     * Require admin role
     */
    public function requireAdmin(): User
    {
        $user = $this->requireAuth();
        if (!$user->isAdmin()) {
            throw new ForbiddenException('Admin access required.');
        }
        return $user;
    }

    /**
     * Require super admin role
     */
    public function requireSuperAdmin(): User
    {
        $user = $this->requireAuth();
        
        if ($this->authorizationService === null) {
            throw new ForbiddenException('Authorization service not available.');
        }
        
        if (!$this->authorizationService->userHasRole($user, 'super_admin')) {
            throw new ForbiddenException('Super Admin access required.');
        }
        
        return $user;
    }

    /**
     * Check if current user has a specific permission
     *
     * @param string $permission Permission name (e.g., 'articles.create')
     * @return bool
     */
    public function can(string $permission): bool
    {
        if ($this->authorizationService === null) {
            return false;
        }
        
        $user = $this->getCurrentUser();
        if ($user === null) {
            return false;
        }
        
        return $this->authorizationService->userCan($user, $permission);
    }

    /**
     * Check if current user has a specific role
     *
     * @param string $roleName Role name (e.g., 'admin')
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        if ($this->authorizationService === null) {
            return false;
        }
        
        $user = $this->getCurrentUser();
        if ($user === null) {
            return false;
        }
        
        return $this->authorizationService->userHasRole($user, $roleName);
    }

    /**
     * Require a specific permission (throw exception if not authorized)
     *
     * @param string $permission Permission name (e.g., 'articles.create')
     * @return User
     * @throws UnauthorizedException
     * @throws ForbiddenException
     */
    public function requirePermission(string $permission): User
    {
        $user = $this->requireAuth();
        
        if ($this->authorizationService === null) {
            throw new ForbiddenException('Authorization service not available.');
        }
        
        if (!$this->authorizationService->userCan($user, $permission)) {
            throw new ForbiddenException("Permission required: {$permission}");
        }
        
        return $user;
    }

    /**
     * Require a specific role (throw exception if not authorized)
     *
     * @param string $roleName Role name (e.g., 'admin')
     * @return User
     * @throws UnauthorizedException
     * @throws ForbiddenException
     */
    public function requireRole(string $roleName): User
    {
        $user = $this->requireAuth();
        
        if ($this->authorizationService === null) {
            throw new ForbiddenException('Authorization service not available.');
        }
        
        if (!$this->authorizationService->userHasRole($user, $roleName)) {
            throw new ForbiddenException("Role required: {$roleName}");
        }
        
        return $user;
    }

    /**
     * Set authorization service (for dependency injection)
     *
     * @param AuthorizationService $authorizationService
     * @return void
     */
    public function setAuthorizationService(AuthorizationService $authorizationService): void
    {
        $this->authorizationService = $authorizationService;
    }

    /**
     * Check if session has expired
     *
     * @return bool
     */
    private function isSessionExpired(): bool
    {
        if (!isset($_SESSION['last_activity'])) {
            return true;
        }

        $inactiveTime = time() - $_SESSION['last_activity'];
        
        return $inactiveTime > self::SESSION_TIMEOUT;
    }

    /**
     * Update last activity timestamp
     *
     * @return void
     */
    private function updateLastActivity(): void
    {
        $_SESSION['last_activity'] = time();
    }

    /**
     * Get session timeout in seconds
     *
     * @return int
     */
    public function getSessionTimeout(): int
    {
        return self::SESSION_TIMEOUT;
    }

    /**
     * Get remaining session time in seconds
     *
     * @return int
     */
    public function getRemainingSessionTime(): int
    {
        if (!isset($_SESSION['last_activity'])) {
            return 0;
        }

        $elapsed = time() - $_SESSION['last_activity'];
        $remaining = self::SESSION_TIMEOUT - $elapsed;

        return max(0, $remaining);
    }
}

