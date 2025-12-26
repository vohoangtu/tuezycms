<?php

declare(strict_types=1);

namespace Modules\Security\Application\Service;

use Shared\Infrastructure\Exception\BadRequestException;

class SecureUploadService
{
    private array $blockedExtensions = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
        'pl', 'py', 'cgi', 'asp', 'aspx', 'exe', 'sh', 'bash', 'bat', 'cmd', 'vbs'
    ];

    private array $allowedMimeTypes = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif'],
        'image/webp' => ['webp'],
        'video/mp4' => ['mp4'],
        'application/pdf' => ['pdf'],
    ];

    /**
     * Validate file for security threats
     * @param array $file $_FILES item
     * @throws BadRequestException
     */
    public function validate(array $file): void
    {
        $filename = $file['name'];
        $tmpPath = $file['tmp_name'];

        // 1. Double Extension & Executable check
        $parts = explode('.', $filename);
        $extension = strtolower(end($parts));

        // Check for double extension attacks (e.g. image.php.jpg)
        // We iterate through ALL parts of the filename
        foreach ($parts as $part) {
            if (in_array(strtolower($part), $this->blockedExtensions, true)) {
                 throw new BadRequestException("Security Violation: Filename contains blocked extension '{$part}'");
            }
        }

        // 2. MIME Type Consistency Check
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);

        if (!array_key_exists($detectedMime, $this->allowedMimeTypes)) {
            throw new BadRequestException("Security Violation: File type '{$detectedMime}' is not allowed.");
        }

        // Check if extension matches MIME
        if (!in_array($extension, $this->allowedMimeTypes[$detectedMime], true)) {
             throw new BadRequestException("Security Violation: Extension '{$extension}' does not match MIME type '{$detectedMime}'.");
        }

        // 3. Content Content Scan (PHP Tags check)
        // Scan first 4KB and last 4KB for <?php tags to prevent embedded scripts
        $handle = fopen($tmpPath, 'rb');
        if ($handle) {
            $startBytes = fread($handle, 4096);
            fseek($handle, -4096, SEEK_END);
            $endBytes = fread($handle, 4096);
            fclose($handle);

            $content = $startBytes . $endBytes;
            if (strpos($content, '<?php') !== false || strpos($content, '<?=') !== false) {
                 throw new BadRequestException("Security Violation: Suspicious PHP code detected in file content.");
            }
            if (strpos($content, '<script') !== false && $detectedMime !== 'application/pdf') {
                 // PDFs can legitimate have scripts for forms, but images shouldn't
                 throw new BadRequestException("Security Violation: Suspicious Script tag detected in file content.");
            }
        }
    }
}
