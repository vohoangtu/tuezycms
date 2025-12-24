<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Media;

class ImageProcessor
{
    private array $thumbnailSizes;
    private int $quality;
    private int $maxWidth;
    private int $maxHeight;

    public function __construct()
    {
        $this->thumbnailSizes = [
            '150x150' => ['width' => 150, 'height' => 150],
            '300x300' => ['width' => 300, 'height' => 300],
            '800x800' => ['width' => 800, 'height' => 800],
        ];
        $this->quality = 85;
        $this->maxWidth = 2000;
        $this->maxHeight = 2000;
    }

    /**
     * Process image: resize if needed and create thumbnails
     */
    public function process(string $sourcePath, string $destinationPath, string $thumbnailBasePath): array
    {
        $imageInfo = $this->getImageInfo($sourcePath);
        if (!$imageInfo) {
            throw new \RuntimeException('Invalid image file');
        }

        $width = $imageInfo['width'];
        $height = $imageInfo['height'];

        // Resize if too large
        if ($width > $this->maxWidth || $height > $this->maxHeight) {
            $this->resizeImage($sourcePath, $destinationPath, $this->maxWidth, $this->maxHeight);
            $imageInfo = $this->getImageInfo($destinationPath);
            $width = $imageInfo['width'];
            $height = $imageInfo['height'];
        } else {
            copy($sourcePath, $destinationPath);
        }

        // Generate thumbnails
        $thumbnails = [];
        foreach ($this->thumbnailSizes as $size => $dimensions) {
            $thumbPath = $thumbnailBasePath . '/' . $size . '/' . basename($destinationPath);
            $this->createThumbnail($destinationPath, $thumbPath, $dimensions['width'], $dimensions['height']);
            $thumbnails[$size] = $thumbPath;
        }

        return [
            'width' => $width,
            'height' => $height,
            'thumbnails' => $thumbnails,
        ];
    }

    /**
     * Get image information
     */
    public function getImageInfo(string $path): ?array
    {
        if (!file_exists($path)) {
            return null;
        }

        $info = getimagesize($path);
        if ($info === false) {
            return null;
        }

        return [
            'width' => $info[0],
            'height' => $info[1],
            'mime' => $info['mime'],
        ];
    }

    /**
     * Resize image maintaining aspect ratio
     */
    private function resizeImage(string $sourcePath, string $destinationPath, int $maxWidth, int $maxHeight): void
    {
        $imageInfo = $this->getImageInfo($sourcePath);
        if (!$imageInfo) {
            return;
        }

        $width = $imageInfo['width'];
        $height = $imageInfo['height'];
        $mime = $imageInfo['mime'];

        // Calculate new dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        // Create image resource
        $source = $this->createImageResource($sourcePath, $mime);
        if (!$source) {
            return;
        }

        // Create new image
        $destination = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG and GIF
        if ($mime === 'image/png' || $mime === 'image/gif') {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
            imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Resize
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save
        $this->saveImage($destination, $destinationPath, $mime);

        imagedestroy($source);
        imagedestroy($destination);
    }

    /**
     * Create thumbnail
     */
    private function createThumbnail(string $sourcePath, string $destinationPath, int $width, int $height): void
    {
        $imageInfo = $this->getImageInfo($sourcePath);
        if (!$imageInfo) {
            return;
        }

        $sourceWidth = $imageInfo['width'];
        $sourceHeight = $imageInfo['height'];
        $mime = $imageInfo['mime'];

        // Calculate dimensions (crop to square or maintain aspect ratio)
        $ratio = max($width / $sourceWidth, $height / $sourceHeight);
        $newWidth = (int)($sourceWidth * $ratio);
        $newHeight = (int)($sourceHeight * $ratio);

        // Create image resource
        $source = $this->createImageResource($sourcePath, $mime);
        if (!$source) {
            return;
        }

        // Create thumbnail
        $thumbnail = imagecreatetruecolor($width, $height);

        // Preserve transparency
        if ($mime === 'image/png' || $mime === 'image/gif') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefilledrectangle($thumbnail, 0, 0, $width, $height, $transparent);
        } else {
            $white = imagecolorallocate($thumbnail, 255, 255, 255);
            imagefill($thumbnail, 0, 0, $white);
        }

        // Calculate position to center
        $x = (int)(($width - $newWidth) / 2);
        $y = (int)(($height - $newHeight) / 2);

        // Resize and center
        imagecopyresampled($thumbnail, $source, $x, $y, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);

        // Save
        $this->saveImage($thumbnail, $destinationPath, $mime);

        imagedestroy($source);
        imagedestroy($thumbnail);
    }

    /**
     * Create image resource from file
     */
    private function createImageResource(string $path, string $mime): ?\GdImage
    {
        $resource = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/gif' => @imagecreatefromgif($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            default => null,
        };
        
        return $resource instanceof \GdImage ? $resource : null;
    }

    /**
     * Save image to file
     */
    private function saveImage(\GdImage $image, string $path, string $mime): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        match ($mime) {
            'image/jpeg', 'image/jpg' => imagejpeg($image, $path, $this->quality),
            'image/png' => imagepng($image, $path, 9),
            'image/gif' => imagegif($image, $path),
            'image/webp' => imagewebp($image, $path, $this->quality),
            default => throw new \RuntimeException('Unsupported image format: ' . $mime),
        };
    }
}

