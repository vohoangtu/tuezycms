<?php

declare(strict_types=1);

namespace Modules\Article\Presentation\Controller;

use Modules\Article\Application\Service\ArticleService;
use Shared\Infrastructure\Controller\BaseController;
use Shared\Infrastructure\I18n\Translator;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Modules\User\Application\Service\AuthService;
use Core\Routing\Router;
use Shared\Infrastructure\Exception\NotFoundException;

class ArticleViewController extends BaseController
{
    private ArticleService $articleService;
    private Translator $translator;
    private Router $router;

    public function __construct(
        AuthService $authService,
        KeyValidator $keyValidator,
        Request $request,
        Response $response,
        ArticleService $articleService,
        Router $router
    ) {
        parent::__construct($authService, $keyValidator, $request, $response);
        $this->articleService = $articleService;
        $this->translator = Translator::getInstance();
        $this->router = $router;
    }

    public function index(): void
    {
        // Replicating logic from index.php: 'articles' handler
        // $typeSlug = $route['type_slug'] ?? 'tin-tuc';
        
        // We get typeSlug from the path which is matched by route
        // e.g. /tin-tuc -> slug is tin-tuc
        $typeSlug = $this->router->getPath(); 
        
        // Default fallback if somehow empty (shouldn't happen with current routing)
        if (empty($typeSlug)) {
             $typeSlug = 'tin-tuc';
        }

        $articles = $this->articleService->listArticlesByTypeSlug($typeSlug, 20, 0, $this->router->getLocale());
            
        $this->response->view('articles', [
            'articles' => $articles,
            'typeSlug' => $typeSlug,
            'translator' => $this->translator,
            'router' => $this->router
        ]);
    }

    public function show(string $type, string $slug): void
    {
        // Replicating logic from index.php: 'article_detail' handler
        // $slug = $route['slug'] ?? '';
        
        $article = $this->articleService->getArticleBySlug($slug, $this->router->getLocale());
            
        if ($article) {
            $this->response->view('article', [
                'article' => $article,
                'router' => $this->router,
                'translator' => $this->translator
            ]);
        } else {
            throw new NotFoundException("Article not found");
        }
    }
}
