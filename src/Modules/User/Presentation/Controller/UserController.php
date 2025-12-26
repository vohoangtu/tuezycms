<?php

declare(strict_types=1);

namespace Modules\User\Presentation\Controller;

use Shared\Infrastructure\Controller\BaseController;
use Modules\User\Infrastructure\Repository\UserRepository;
use Modules\Authorization\Infrastructure\Repository\RoleRepository;
use Modules\User\Domain\Model\User;
use Modules\User\Domain\Event\UserCreatedEvent;
use Modules\User\Domain\Event\UserUpdatedEvent;
use Modules\User\Domain\Event\UserDeletedEvent;
use Shared\Infrastructure\Event\EventDispatcher;
use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Database\DB;
use Shared\Infrastructure\Cache\Cache;

class UserController extends BaseController
{
    public function __construct(
        AuthService $authService,
        KeyValidator $keyValidator,
        Request $request,
        Response $response,
        private UserRepository $userRepository,
        private RoleRepository $roleRepository
    ) {
        parent::__construct($authService, $keyValidator, $request, $response);
    }

    /**
     * GET /admin/api/users
     * List all users with advanced filters, sorting, pagination
     */
    public function index(): void
    {
        // Get filters
        $search = $_GET['search'] ?? '';
        $roleFilter = $_GET['role'] ?? '';
        $statusFilter = $_GET['status'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'created_at';
        $sortDir = strtoupper($_GET['sort_dir'] ?? 'DESC');
        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 20);

        // Validate sort direction
        $sortDir = in_array($sortDir, ['ASC', 'DESC']) ? $sortDir : 'DESC';
        
        // Validate sort column
        $allowedSort = ['id', 'email', 'full_name', 'is_active', 'created_at'];
        if (!in_array($sortBy, $allowedSort)) {
            $sortBy = 'created_at';
        }

        // Build base query
        $query = DB::table('users as u')
            ->select('u.id', 'u.email', 'u.full_name', 'u.is_active', 'u.created_at');

        // Apply search filter
        if ($search) {
            $query->where('u.email', 'LIKE', "%{$search}%");
            // Note: For OR condition, we'll need to enhance QueryBuilder
            // For now, search only by email
        }

        // Apply role filter
        if ($roleFilter) {
            $query->join('user_roles as ur', 'u.id', '=', 'ur.user_id')
                  ->where('ur.role_id', '=', (int)$roleFilter);
        }

        // Apply status filter
        if ($statusFilter !== '') {
            $query->where('u.is_active', '=', (int)$statusFilter);
        }

        // Apply date range filter
        if ($dateFrom) {
            $query->where('u.created_at', '>=', $dateFrom . ' 00:00:00');
        }
        
        if ($dateTo) {
            $query->where('u.created_at', '<=', $dateTo . ' 23:59:59');
        }

        // Get total count before pagination
        $countQuery = clone $query;
        $total = count($countQuery->get());

        // Apply sorting
        $query->orderBy("u.{$sortBy}", $sortDir);

        // Apply pagination
        $query->limit($perPage)->offset(($page - 1) * $perPage);
        
        // Get paginated results
        $users = $query->get();

        // Get roles for each user
        foreach ($users as &$user) {
            $user['roles'] = $this->userRepository->getRoles($user['id']);
        }

        $this->json([
            'success' => true,
            'data' => $users,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => (int)ceil($total / $perPage),
                'from' => (($page - 1) * $perPage) + 1,
                'to' => min($page * $perPage, $total)
            ],
            'filters' => [
                'search' => $search,
                'role' => $roleFilter,
                'status' => $statusFilter,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'sortBy' => $sortBy,
                'sortDir' => $sortDir
            ]
        ]);
    }

    /**
     * GET /admin/api/users/{id}
     * Get single user
     */
    public function show(int $id): void
    {
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }

        $roles = $this->userRepository->getRoles($id);

        $this->json([
            'success' => true,
            'data' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'full_name' => $user->getFullName(),
                'is_active' => $user->isActive(),
                'roles' => $roles
            ]
        ]);
    }

    /**
     * POST /admin/api/users
     * Create new user
     */
    public function store(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        // Validate
        if (empty($data['email']) || empty($data['password'])) {
            $this->json(['success' => false, 'message' => 'Email and password are required'], 400);
            return;
        }

        // Check unique email
        if ($this->userRepository->findByEmail($data['email'])) {
            $this->json(['success' => false, 'message' => 'Email already exists'], 400);
            return;
        }

        // Determine legacy role: if admin role is selected, set role='admin'
        $legacyRole = 'user';
        if (!empty($data['roles'])) {
            // Check if any selected role is admin (assumed role ID 1=Super Admin, 2=Admin)
            // Ideally we fetch role names, but for now we trust IDs or key names.
            // Better: Fetch roles from DB to check names.
            foreach ($data['roles'] as $roleId) {
                if (in_array((int)$roleId, [1, 2])) {
                    $legacyRole = 'admin';
                    break;
                }
            }
        }

        // Create user
        $user = new User(
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['full_name'] ?? '',
            $legacyRole
        );
        
        if (isset($data['is_active'])) {
             $user->setIsActive((bool)$data['is_active']);
        }

        $this->userRepository->save($user);

        //Sync roles
        if (!empty($data['roles'])) {
            $this->userRepository->syncRoles($user->getId(), $data['roles']);
        }

        // Dispatch UserCreatedEvent  
        EventDispatcher::getInstance()->dispatch(new UserCreatedEvent($user));

        // Event dispatched in repository
        $this->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => [
                'id' => $user->getId(),
                'email' => $user->getEmail()
            ]
        ]);
    }

    /**
     * PUT /admin/api/users/{id}
     * Update user
     */
    public function update(int $id): void
    {
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Update user fields
        if (isset($data['full_name'])) {
            $user->setFullName($data['full_name']);
        }

        if (isset($data['is_active'])) {
            $user->setIsActive((bool)$data['is_active']);
        }

        if (!empty($data['password'])) {
            $user->setPasswordHash(password_hash($data['password'], PASSWORD_BCRYPT));
        }

        $this->userRepository->save($user);

        // Sync roles
        if (isset($data['roles'])) {
            $this->userRepository->syncRoles($id, $data['roles']);
        }

        // Dispatch UserUpdatedEvent
        $changes = array_keys(array_filter([
            'full_name' => isset($data['full_name']),
            'is_active' => isset($data['is_active']),
            'password' => !empty($data['password']),
            'roles' => isset($data['roles'])
        ]));
        EventDispatcher::getInstance()->dispatch(new UserUpdatedEvent($user, $changes));

        $this->json([
            'success' => true,
            'message' => 'User updated successfully'
        ]);
    }

    /**
     * DELETE /admin/api/users/{id}
     * Delete user
     */
    public function destroy(int $id): void
    {
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }

        // Don't allow deleting yourself
        if ($id === $this->getCurrentUserId()) {
            $this->json(['success' => false, 'message' => 'Cannot delete yourself'], 400);
            return;
        }

        $email = $user->getEmail();
        $this->userRepository->delete($id);

        // Dispatch UserDeletedEvent
        EventDispatcher::getInstance()->dispatch(new UserDeletedEvent($id, $email));

        // Event dispatched in repository
        $this->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }
    
    /**
     * POST /admin/api/users/bulk-activate
     * Bulk activate users
     */
    public function bulkActivate(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $userIds = $data['user_ids'] ?? [];
        
        if (empty($userIds)) {
            $this->json(['success' => false, 'message' => 'No users selected'], 400);
            return;
        }
        
        DB::table('users')
            ->whereIn('id', $userIds)
            ->update(['is_active' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
        
        // Clear cache for affected users
        foreach ($userIds as $userId) {
            Cache::delete("user:{$userId}");
        }
        Cache::delete('users:all');
        
        $this->json([
            'success' => true,
            'message' => count($userIds) . ' users activated',
            'count' => count($userIds)
        ]);
    }
    
    /**
     * POST /admin/api/users/bulk-deactivate
     * Bulk deactivate users
     */
    public function bulkDeactivate(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $userIds = $data['user_ids'] ?? [];
        
        if (empty($userIds)) {
            $this->json(['success' => false, 'message' => 'No users selected'], 400);
            return;
        }
        
        // Don't allow deactivating yourself
        $currentUserId = $this->getCurrentUserId();
        if (in_array($currentUserId, $userIds)) {
            $this->json(['success' => false, 'message' => 'Cannot deactivate yourself'], 400);
            return;
        }
        
        DB::table('users')
            ->whereIn('id', $userIds)
            ->update(['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        
        // Clear cache
        foreach ($userIds as $userId) {
            Cache::delete("user:{$userId}");
        }
        Cache::delete('users:all');
        
        $this->json([
            'success' => true,
            'message' => count($userIds) . ' users deactivated',
            'count' => count($userIds)
        ]);
    }
    
    /**
     * POST /admin/api/users/bulk-delete
     * Bulk delete users
     */
    public function bulkDelete(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $userIds = $data['user_ids'] ?? [];
        
        if (empty($userIds)) {
            $this->json(['success' => false, 'message' => 'No users selected'], 400);
            return;
        }
        
        // Don't allow deleting yourself
        $currentUserId = $this->getCurrentUserId();
        if (in_array($currentUserId, $userIds)) {
            $this->json(['success' => false, 'message' => 'Cannot delete yourself'], 400);
            return;
        }
        
        DB::transaction(function() use ($userIds) {
            // Delete user roles first
            DB::table('user_roles')->whereIn('user_id', $userIds)->delete();
            
            // Delete users
            DB::table('users')->whereIn('id', $userIds)->delete();
        });
        
        // Clear cache
        foreach ($userIds as $userId) {
            Cache::delete("user:{$userId}");
            Cache::delete("user:{$userId}:roles");
            Cache::delete("user:{$userId}:permissions");
        }
        Cache::delete('users:all');
        
        $this->json([
            'success' => true,
            'message' => count($userIds) . ' users deleted',
            'count' => count($userIds)
        ]);
    }
    
    /**
     * POST /admin/api/users/bulk-assign-role
     * Bulk assign role to users
     */
    public function bulkAssignRole(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $userIds = $data['user_ids'] ?? [];
        $roleId = $data['role_id'] ?? null;
        
        if (empty($userIds) || !$roleId) {
            $this->json(['success' => false, 'message' => 'Invalid data'], 400);
            return;
        }
        
        // Verify role exists
        $role = $this->roleRepository->findById((int)$roleId);
        if (!$role) {
            $this->json(['success' => false, 'message' => 'Role not found'], 404);
            return;
        }
        
        DB::transaction(function() use ($userIds, $roleId) {
            foreach ($userIds as $userId) {
                // Check if already has role
                $exists = DB::table('user_roles')
                    ->where('user_id', '=', $userId)
                    ->where('role_id', '=', $roleId)
                    ->first();
                
                if (!$exists) {
                    DB::table('user_roles')->insert([
                        'user_id' => $userId,
                        'role_id' => $roleId
                    ]);
                }
                
                // Clear cache
                Cache::delete("user:{$userId}:roles");
                Cache::delete("user:{$userId}:permissions");
            }
        });
        
        $this->json([
            'success' => true,
            'message' => "Role '{$role->getDisplayName()}' assigned to " . count($userIds) . ' users'
        ]);
    }
    
    /**
     * GET /admin/api/users/{id}/details
     * Get user full details
     */
    public function getUserDetails(int $id): void
    {
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }
        
        $roles = $this->userRepository->getRoles($id);
        
        // Get user stats
        $stats = [
            'total_roles' => count($roles),
            'is_active' => $user->isActive(),
            'created_at' => $user->getCreatedAt()?->format('Y-m-d H:i:s'),
            'last_login' => $user->getLastLoginAt()?->format('Y-m-d H:i:s')
        ];
        
        $this->json([
            'success' => true,
            'data' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'full_name' => $user->getFullName(),
                'is_active' => $user->isActive(),
                'roles' => $roles,
                'stats' => $stats
            ]
        ]);
    }
    
    /**
     * POST /admin/api/users/{id}/reset-password
     * Reset user password
     */
    public function resetPassword(int $id): void
    {
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }
        
        // Generate random password
        $newPassword = bin2hex(random_bytes(8));
        $user->setPasswordHash(password_hash($newPassword, PASSWORD_BCRYPT));
        $this->userRepository->save($user);
        
        // TODO: Send email with new password
        
        $this->json([
            'success' => true,
            'message' => 'Password reset successfully',
            'new_password' => $newPassword // In production, send via email only
        ]);
    }
    
    /**
     * POST /admin/api/users/{id}/send-email
     * Send email to user
     */
    public function sendEmail(int $id): void
    {
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $subject = $data['subject'] ?? '';
        $message = $data['message'] ?? '';
        
        if (empty($subject) || empty($message)) {
            $this->json(['success' => false, 'message' => 'Subject and message required'], 400);
            return;
        }
        
        // TODO: Implement email sending
        
        $this->json([
            'success' => true,
            'message' => 'Email sent to ' . $user->getEmail()
        ]);
    }
    
    /**
     * GET /admin/api/users/export
     * Export users to CSV
     */
    public function exportCsv(): void
    {
        $type = $_GET['type'] ?? 'all';
        $userIds = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];
        
        // Build query
        $query = DB::table('users as u')
            ->select('u.id', 'u.email', 'u.full_name', 'u.is_active', 'u.created_at');
        
        if ($type === 'selected' && !empty($userIds)) {
            $query->whereIn('u.id', $userIds);
        } elseif ($type === 'filtered') {
            // Apply same filters as index
            $search = $_GET['search'] ?? '';
            $roleFilter = $_GET['role'] ?? '';
            $statusFilter = $_GET['status'] ?? '';
            
            if ($search) {
                $query->where('u.email', 'LIKE', "%{$search}%");
            }
            if ($roleFilter) {
                $query->join('user_roles as ur', 'u.id', '=', 'ur.user_id')
                      ->where('ur.role_id', '=', (int)$roleFilter);
            }
            if ($statusFilter !== '') {
                $query->where('u.is_active', '=', (int)$statusFilter);
            }
        }
        
        $users = $query->get();
        
        // Set headers
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="users_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers
        fputcsv($output, ['ID', 'Email', 'Full Name', 'Status', 'Created At']);
        
        // Data
        foreach ($users as $user) {
            fputcsv($output, [
                $user['id'],
                $user['email'],
                $user['full_name'] ?? '',
                $user['is_active'] ? 'Active' : 'Inactive',
                $user['created_at']
            ]);
        }
        
        fclose($output);
        exit;
    }
}
