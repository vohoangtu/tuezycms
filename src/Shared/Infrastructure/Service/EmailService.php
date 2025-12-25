<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Service;

use Shared\Infrastructure\Database\DB;

/**
 * Email Service
 * Sends emails using SMTP configuration
 */
class EmailService
{
    /**
     * Send email
     */
    public function send(string $to, string $subject, string $body, array $options = []): bool
    {
        // Check if email notifications are enabled
        $config = DB::table('configurations')
            ->where('name', '=', 'email_notifications')
            ->first();

        if (!$config || !$config['is_enabled']) {
            error_log('Email notifications are disabled');
            return false;
        }

        $configData = json_decode($config['config'] ?? '{}', true);
        
        // For now, use PHP's mail() function
        // TODO: Integrate PHPMailer for SMTP support
        $headers = [
            'From' => $options['from'] ?? 'noreply@tuzycms.com',
            'Reply-To' => $options['reply_to'] ?? 'noreply@tuzycms.com',
            'X-Mailer' => 'TuzyCMS',
            'Content-Type' => 'text/html; charset=UTF-8'
        ];

        $headerString = '';
        foreach ($headers as $key => $value) {
            $headerString .= "{$key}: {$value}\r\n";
        }

        try {
            $result = mail($to, $subject, $body, $headerString);
            
            if ($result) {
                error_log("Email sent successfully to: {$to}");
            } else {
                error_log("Failed to send email to: {$to}");
            }
            
            return $result;
        } catch (\Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email using template
     */
    public function sendTemplate(string $to, string $template, array $data = []): bool
    {
        // TODO: Implement template rendering
        $subject = $data['subject'] ?? 'Notification from TuzyCMS';
        $body = $this->renderTemplate($template, $data);
        
        return $this->send($to, $subject, $body);
    }

    /**
     * Render email template
     */
    private function renderTemplate(string $template, array $data): string
    {
        // Simple template rendering
        $templatePath = __DIR__ . "/../../Presentation/View/emails/{$template}.php";
        
        if (!file_exists($templatePath)) {
            return $data['message'] ?? 'No content';
        }

        ob_start();
        extract($data);
        include $templatePath;
        return ob_get_clean();
    }

    /**
     * Get SMTP configuration
     */
    public function getSmtpConfig(): array
    {
        $config = DB::table('configurations')
            ->where('name', '=', 'email_notifications')
            ->first();

        if (!$config) {
            return [];
        }

        return json_decode($config['config'] ?? '{}', true);
    }
}
