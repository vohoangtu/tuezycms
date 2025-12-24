<?php

declare(strict_types=1);

namespace Modules\Media\Presentation\Controller;

use Modules\User\Application\Service\AuthService;
use Modules\Media\Application\Service\MediaService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Controller\BaseController;

class MediaPageController extends BaseController
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

    /**
     * Show media library page
     */
    public function index(): void
    {
        $mediaFiles = $this->mediaService->listMedia(50);

        $this->render('admin/media', [
            'mediaFiles' => $mediaFiles,
            'mediaService' => $this->mediaService
        ]);
    }
}
