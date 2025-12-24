<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Media;

use TuzyCMS\Domain\Media\MediaFile;
use TuzyCMS\Domain\Media\MediaType;
use Shared\Infrastructure\Storage\FileStorage;
use Shared\Infrastructure\Storage\LocalFileStorage;

class FileUploader
{
    private FileStorage $storage;
    private ImageProcessor $imageProcessor;
    private int $maxFileSize;
    private array $allowedMimeTypes;

    public function __construct(FileStorage $storage, ImageProcessor $imageProcessor)
    {
        $this->storage = $storage;
        $this->imageProcessor = $imageProcessor;
        $this->maxFileSize = 10 * 1024 * 1024; // 10MB
        $this->allowedMimeTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'video/mp4',
            'application/pdf',
        ];
    }

    /**
     * Upload and process a file
     */
    public function upload(array $file, ?int $userId = null): MediaFile
    {
        // Validate file
        $this->validateFile($file);

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $this->generateFilename($extension);
        $originalFilename = $this->sanitizeFilename($file['name']);

        // Determine storage path
        $year = date('Y');
        $month = date('m');
        $storagePath = $year . '/' . $month . '/' . $filename;

        // Determine media type
        $mimeType = $file['type'];
        $mediaType = MediaType::fromMimeType($mimeType);

        // Process and store file
        $fullPath = $this->storage->getPath($storagePath);
        $thumbnails = [];

        if ($mediaType === MediaType::IMAGE) {
            // Process image (resize, create thumbnails)
            $thumbnailBasePath = 'thumbnails';
            $result = $this->imageProcessor->process($file['tmp_name'], $fullPath, $this->storage->getPath($thumbnailBasePath));
            $width = $result['width'];
            $height = $result['height'];
            $thumbnails = $result['thumbnails'];
        } else {
            // Just copy the file
            $this->storage->store($file['tmp_name'], $storagePath);
            $width = null;
            $height = null;
        }

        // Create MediaFile entity
        $mediaFile = new MediaFile(
            $filename,
            $originalFilename,
            $storagePath,
            $mediaType,
            $mimeType,
            (int)$file['size']
        );

        $mediaFile->setWidth($width);
        $mediaFile->setHeight($height);
        $mediaFile->setCreatedBy($userId);

        if (!empty($thumbnails)) {
            $mediaFile->setThumbnailPath($thumbnails['150x150'] ?? null);
        }

        return $mediaFile;
    }

    /**
     * Validate uploaded file
     */
    private function validateFile(array $file): void
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new \RuntimeException('Invalid file upload');
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new \RuntimeException('File size exceeds maximum allowed size');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('File upload error: ' . $file['error']);
        }

        // Validate MIME type
        $mimeType = $file['type'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($detectedMimeType, $this->allowedMimeTypes, true)) {
            throw new \RuntimeException('File type not allowed: ' . $detectedMimeType);
        }

        // Double check with extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'pdf'];
        if (!in_array($extension, $allowedExtensions, true)) {
            throw new \RuntimeException('File extension not allowed: ' . $extension);
        }
    }

    /**
     * Generate unique filename
     */
    private function generateFilename(string $extension): string
    {
        return uniqid('', true) . '_' . time() . '.' . $extension;
    }

    /**
     * Sanitize filename
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove path components
        $filename = basename($filename);
        
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        return $filename;
    }
}

