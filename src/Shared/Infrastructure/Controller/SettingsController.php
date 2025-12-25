<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Controller;

use Modules\User\Application\Service\AuthService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Database\DB;

// Event-Driven imports
use Shared\Infrastructure\Event\EventDispatcher;
use Shared\Domain\Event\ConfigurationToggledEvent;
use Shared\Domain\Event\ConfigurationUpdatedEvent;

/**
 * Settings/Configurations Controller
 * Handles configuration items that can be toggled on/off
 */
class SettingsController extends BaseController
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
     * Get all configurations or by category
     */
    public function index(): void
    {
        $category = $this->request->get('category');
        
        $query = DB::table('configurations');
        
        if ($category && $category !== 'all') {
            $query->where('category', '=', $category);
        }
        
        $configurations = $query->orderBy('category')
                               ->orderBy('display_name')
                               ->get();
        
        $this->json([
            'success' => true,
            'data' => $configurations
        ]);
    }

    /**
     * Get single configuration
     */
    public function show(int $id): void
    {
        $config = DB::table('configurations')->find($id);
        
        if (!$config) {
            $this->json([
                'success' => false,
                'message' => 'Configuration not found'
            ], 404);
            return;
        }
        
        // Parse JSON config
        if ($config['config']) {
            $config['config'] = json_decode($config['config'], true);
        }
        
        $this->json([
            'success' => true,
            'data' => $config
        ]);
    }

    /**
     * Toggle configuration on/off
     */
    public function toggle(int $id): void
    {
        $data = $this->request->json();
        $isEnabled = $data['is_enabled'] ?? null;
        
        if ($isEnabled === null) {
            $this->json([
                'success' => false,
                'message' => 'Missing is_enabled parameter'
            ], 400);
            return;
        }
        
        $config = DB::table('configurations')->find($id);
        
        if (!$config) {
            $this->json([
                'success' => false,
                'message' => 'Configuration not found'
            ], 404);
            return;
        }
        
        DB::table('configurations')
            ->where('id', '=', $id)
            ->update([
                'is_enabled' => $isEnabled ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        
        // Dispatch ConfigurationToggledEvent
        EventDispatcher::getInstance()->dispatch(
            new ConfigurationToggledEvent($id, $config['name'], (bool)$isEnabled)
        );
        
        $this->json([
            'success' => true,
            'message' => $isEnabled ? 'Configuration enabled' : 'Configuration disabled'
        ]);
    }

    /**
     * Update configuration settings
     */
    public function updateConfig(int $id): void
    {
        $data = $this->request->json();
        $config = $data['config'] ?? null;
        
        if ($config === null) {
            $this->json([
                'success' => false,
                'message' => 'Missing config parameter'
            ], 400);
            return;
        }
        
        $existing = DB::table('configurations')->find($id);
        
        if (!$existing) {
            $this->json([
                'success' => false,
                'message' => 'Configuration not found'
            ], 404);
            return;
        }
        
        DB::table('configurations')
            ->where('id', '=', $id)
            ->update([
                'config' => json_encode($config),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        
        // Dispatch ConfigurationUpdatedEvent
        EventDispatcher::getInstance()->dispatch(
            new ConfigurationUpdatedEvent($id, $existing['name'], $config)
        );
        
        $this->json([
            'success' => true,
            'message' => 'Configuration updated successfully'
        ]);
    }

    /**
     * Store new configuration
     */
    public function store(): void
    {
        $data = $this->request->json();
        
        $required = ['name', 'display_name', 'category'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->json([
                    'success' => false,
                    'message' => "Missing required field: {$field}"
                ], 400);
                return;
            }
        }
        
        $id = DB::table('configurations')->insert([
            'name' => $data['name'],
            'display_name' => $data['display_name'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'],
            'is_enabled' => $data['is_enabled'] ?? 1,
            'config' => isset($data['config']) ? json_encode($data['config']) : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->json([
            'success' => true,
            'message' => 'Configuration created successfully',
            'data' => ['id' => $id]
        ]);
    }
}
