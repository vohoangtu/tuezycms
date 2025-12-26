<?php

declare(strict_types=1);

namespace Shared\Presentation\Controller;

use Modules\Article\Application\Service\ArticleService;
use Modules\Product\Infrastructure\Repository\ProductRepository;
use Shared\Infrastructure\Controller\BaseController;
use Shared\Infrastructure\I18n\Translator;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Modules\User\Application\Service\AuthService;
use Core\Routing\Router;
use Shared\Infrastructure\Exception\NotFoundException;

class ContentViewController extends BaseController
{
    private ArticleService $articleService;
    private ProductRepository $productRepository;
    private Translator $translator;
    private Router $router;

    public function __construct(
        AuthService $authService,
        KeyValidator $keyValidator,
        Request $request,
        Response $response,
        ArticleService $articleService,
        ProductRepository $productRepository,
        Router $router
    ) {
        parent::__construct($authService, $keyValidator, $request, $response);
        $this->articleService = $articleService;
        $this->productRepository = $productRepository;
        $this->translator = Translator::getInstance();
        $this->router = $router;
    }

    public function show(string $slug): void
    {
        // Replicating logic from index.php: 'content_detail' handler
        // Priority: Product first, then Article
        
        $locale = $this->router->getLocale();
        
        // Try product first
        $product = $this->productRepository->findBySlug($slug, $locale);
        if ($product) {
            $this->response->view('product', [
                'product' => $product,
                'router' => $this->router,
                'translator' => $this->translator
            ]);
            return;
        }
        
        // If not product, try article
        $article = $this->articleService->getArticleBySlug($slug, $locale);
        if ($article) {
            $this->response->view('article', [
                'article' => $article,
                'router' => $this->router,
                'translator' => $this->translator
            ]);
            return;
        }
        
        // Not found
        throw new NotFoundException("Content not found");
    }
}
