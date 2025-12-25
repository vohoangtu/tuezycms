<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Service;

use Shared\Infrastructure\Database\DB;

/**
 * SMS Service Interface
 * Placeholder for SMS functionality
 */
class SmsService
{
    /**
     * Send SMS
     */
    public function send(string $to, string $message): bool
    {
        // Check if SMS notifications are enabled
        $config = DB::table('configurations')
            ->where('name', '=', 'sms_notifications')
            ->first();

        if (!$config || !$config['is_enabled']) {
            error_log('SMS notifications are disabled');
            return false;
        }

        $configData = json_decode($config['config'] ?? '{}', true);
        $provider = $configData['provider'] ?? 'twilio';

        // TODO: Implement actual SMS sending via Twilio or other provider
        error_log("SMS would be sent to {$to}: {$message} (provider: {$provider})");
        
        // For now, just log
        return true;
    }

    /**
     * Send SMS using template
     */
    public function sendTemplate(string $to, string $template, array $data = []): bool
    {
        $message = $this->renderTemplate($template, $data);
        return $this->send($to, $message);
    }

    /**
     * Render SMS template
     */
    private function renderTemplate(string $template, array $data): string
    {
        // Simple template rendering
        $templates = [
            'otp' => 'Your OTP code is: {code}',
            'notification' => '{message}',
        ];

        $message = $templates[$template] ?? '{message}';

        foreach ($data as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }

        return $message;
    }

    /**
     * Get SMS provider configuration
     */
    public function getProviderConfig(): array
    {
        $config = DB::table('configurations')
            ->where('name', '=', 'sms_notifications')
            ->first();

        if (!$config) {
            return [];
        }

        return json_decode($config['config'] ?? '{}', true);
    }
}
