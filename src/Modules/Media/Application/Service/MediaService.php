<?php

declare(strict_types=1);

namespace Modules\Media\Application\Service;

use TuzyCMS\Domain\Media\MediaFile;
use Shared\Infrastructure\Media\FileUploader;
use Modules\Media\Infrastructure\Repository\MediaRepository;
use Shared\Infrastructure\Storage\LocalFileStorage;

class MediaService
{
    private MediaRepository $mediaRepository;
    private FileUploader $fileUploader;
    private LocalFileStorage $storage;

    public function __construct(
        MediaRepository $mediaRepository,
        FileUploader $fileUploader,
        LocalFileStorage $storage
    ) {
        $this->mediaRepository = $mediaRepository;
        $this->fileUploader = $fileUploader;
        $this->storage = $storage;
    }

    /**
     * Upload a file
     */
    public function uploadFile(array $file, ?int $userId = null): MediaFile
    {
        $mediaFile = $this->fileUploader->upload($file, $userId);
        $this->mediaRepository->save($mediaFile);
        return $mediaFile;
    }

    /**
     * Get media file by ID
     */
    public function getMediaFile(int $id): ?MediaFile
    {
        return $this->mediaRepository->findById($id);
    }

    /**
     * List media files with pagination and filters
     */
    public function listMedia(int $limit = 50, int $offset = 0, ?string $type = null, ?string $search = null): array
    {
        return $this->mediaRepository->findAll($limit, $offset, $type, $search);
    }

    /**
     * Get total count of media files
     */
    public function countMedia(?string $type = null, ?string $search = null): int
    {
        return $this->mediaRepository->count($type, $search);
    }

    /**
     * Delete media file
     */
    public function deleteMedia(int $id): void
    {
        $mediaFile = $this->mediaRepository->findById($id);
        if (!$mediaFile) {
            throw new \RuntimeException('Media file not found');
        }

        // Check usage
        $usage = $this->mediaRepository->getUsage($id);
        if (!empty($usage)) {
            throw new \RuntimeException('Cannot delete media file: it is being used');
        }

        // Delete physical files
        $this->storage->delete($mediaFile->getPath());
        if ($mediaFile->getThumbnailPath()) {
            $this->storage->delete($mediaFile->getThumbnailPath());
        }

        // Delete thumbnails
        if ($mediaFile->getType()->value === 'image') {
            $thumbnailSizes = ['150x150', '300x300', '800x800'];
            foreach ($thumbnailSizes as $size) {
                $thumbPath = 'thumbnails/' . $size . '/' . $mediaFile->getFilename();
                $this->storage->delete($thumbPath);
            }
        }

        // Delete from database
        $this->mediaRepository->delete($id);
    }

    /**
     * Attach media to entity
     */
    public function attachMedia(int $mediaId, string $entityType, string $usageType, ?int $entityId = null, ?string $entityKey = null): void
    {
        $this->mediaRepository->addUsage($mediaId, $entityType, $usageType, $entityId, $entityKey);
    }

    /**
     * Detach media from entity
     */
    public function detachMedia(int $mediaId, string $entityType, string $usageType, ?int $entityId = null, ?string $entityKey = null): void
    {
        $this->mediaRepository->removeUsage($mediaId, $entityType, $usageType, $entityId, $entityKey);
    }

    /**
     * Get media usage
     */
    public function getMediaUsage(int $mediaId): array
    {
        return $this->mediaRepository->getUsage($mediaId);
    }

    /**
     * Get public URL for media file
     */
    public function getMediaUrl(MediaFile $mediaFile, ?string $size = null): string
    {
        if ($size && $mediaFile->getType()->value === 'image') {
            $thumbPath = 'thumbnails/' . $size . '/' . $mediaFile->getFilename();
            if ($this->storage->exists($thumbPath)) {
                return $this->storage->getUrl($thumbPath);
            }
        }

        return $this->storage->getUrl($mediaFile->getPath());
    }
}

