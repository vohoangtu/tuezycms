<?php

declare(strict_types=1);

namespace Modules\Media\Presentation\Controller;

use Modules\User\Application\Service\AuthService;
use Modules\Media\Application\Service\MediaService;
use Shared\Infrastructure\Exception\NotFoundException;
use Shared\Infrastructure\Exception\BadRequestException;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Controller\BaseController;

class MediaController extends BaseController
{
    private MediaService $mediaService;

    public function __construct(
        AuthService $authService,
        KeyValidator $keyValidator,
        Request $request,
        Response $response,
        MediaService $mediaService
    ) {
        parent::__construct($authService, $keyValidator, $request, $response);
        $this->mediaService = $mediaService;
    }

    public function index(): void
    {
        if ($this->request->method() !== 'GET') {
            throw new BadRequestException('Method not allowed');
        }

        $limit = (int)($this->request->get('limit', 24));
        $offset = (int)($this->request->get('offset', 0));
        $type = $this->request->get('type');
        $search = $this->request->get('search', '');

        $mediaFiles = $this->mediaService->listMedia($limit, $offset, $type, $search);
        $total = $this->mediaService->countMedia($type, $search);

        $this->json([
            'files' => array_map(function($file) {
                return [
                    'id' => $file->getId(),
                    'filename' => $file->getFilename(),
                    'original_filename' => $file->getOriginalFilename(),
                    'url' => $this->mediaService->getMediaUrl($file),
                    'thumbnail_url' => $this->mediaService->getMediaUrl($file, '150x150'),
                    'type' => $file->getType()->value,
                    'formatted_size' => $this->formatBytes($file->getSize()),
                    'width' => $file->getWidth(),
                    'height' => $file->getHeight(),
                ];
            }, $mediaFiles),
            'total' => $total,
        ]);
    }

    public function show(int $id): void
    {
        if ($this->request->method() !== 'GET') {
            throw new BadRequestException('Method not allowed');
        }

        $mediaFile = $this->mediaService->getMediaFile($id);
        
        if ($mediaFile === null) {
            throw new NotFoundException('Media file not found');
        }

        $this->json([
            'id' => $mediaFile->getId(),
            'filename' => $mediaFile->getFilename(),
            'original_filename' => $mediaFile->getOriginalFilename(),
            'url' => $this->mediaService->getMediaUrl($mediaFile),
            'thumbnail_url' => $this->mediaService->getMediaUrl($mediaFile, '150x150'),
            'type' => $mediaFile->getType()->value,
            'formatted_size' => $this->formatBytes($mediaFile->getSize()),
            'width' => $mediaFile->getWidth(),
            'height' => $mediaFile->getHeight(),
        ]);
    }

    public function store(): void
    {
        if ($this->request->method() !== 'POST') {
            throw new BadRequestException('Method not allowed');
        }

        $file = $this->request->file('file');
        if ($file === null) {
            throw new BadRequestException('No file uploaded');
        }

        $userId = $this->authService->getCurrentUser()?->getId();
        $mediaFile = $this->mediaService->uploadFile($file, $userId);
        
        // Set alt text and description if provided
        $altText = $this->request->post('alt_text');
        $description = $this->request->post('description');
        
        if ($altText || $description) {
            if ($altText) {
                $mediaFile->setAltText($altText);
            }
            if ($description) {
                $mediaFile->setDescription($description);
            }
            // Re-save to update alt_text and description
            $container = \Core\Container\ServiceContainer::getInstance();
            $mediaRepository = $container->make(\TuzyCMS\Infrastructure\Repository\MediaRepository::class);
            $mediaRepository->save($mediaFile);
        }

        $this->json([
            'success' => true,
            'id' => $mediaFile->getId(),
            'url' => $this->mediaService->getMediaUrl($mediaFile),
        ]);
    }

    public function delete(int $id): void
    {
        if ($this->request->method() !== 'DELETE') {
            throw new BadRequestException('Method not allowed');
        }

        $this->mediaService->deleteMedia($id);
        $this->json(['success' => true]);
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
