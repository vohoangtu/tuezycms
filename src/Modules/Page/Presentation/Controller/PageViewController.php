<?php

declare(strict_types=1);

namespace Modules\Page\Presentation\Controller;

use Modules\Article\Infrastructure\Repository\PageRepository;
use Shared\Infrastructure\Controller\BaseController;
use Shared\Infrastructure\I18n\Translator;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Modules\User\Application\Service\AuthService;
use Core\Routing\Router;
use Shared\Infrastructure\Exception\NotFoundException;

class PageViewController extends BaseController
{
    private PageRepository $pageRepository;
    private Translator $translator;
    private Router $router;

    public function __construct(
        AuthService $authService,
        KeyValidator $keyValidator,
        Request $request,
        Response $response,
        PageRepository $pageRepository,
        Router $router
    ) {
        parent::__construct($authService, $keyValidator, $request, $response);
        $this->pageRepository = $pageRepository;
        $this->translator = Translator::getInstance();
        $this->router = $router;
    }

    public function contact(): void
    {
        // Replicating logic from index.php: 'contact' handler
        // $slug = $route['slug'] ?? 'lien-he';
        
        // Determine slug based on route name or path
        // Legacy: 'lien-he' for VN, 'contact' for EN usually, but implementation showed:
        // 'lien-he' => ['slug' => 'lien-he']
        // 'contact' => ['slug' => 'contact']
        
        // We can check path to decide slug
        $path = $this->router->getPath(); // "contact" or "lien-he"
        $slug = $path === 'contact' ? 'contact' : 'lien-he';

        $page = $this->pageRepository->findBySlug($slug, $this->router->getLocale());
            
        if ($page) {
            $this->response->view('page', [
                'page' => $page,
                'router' => $this->router,
                'translator' => $this->translator
            ]);
        } else {
            throw new NotFoundException("Page not found");
        }
    }
}
