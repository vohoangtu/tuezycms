<?php

declare(strict_types=1);

namespace Modules\Home\Presentation\Controller;

use Modules\Article\Application\Service\ArticleService;
use Modules\Product\Infrastructure\Repository\ProductRepository;
use Shared\Infrastructure\Controller\BaseController;
use Shared\Infrastructure\I18n\Translator;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Modules\User\Application\Service\AuthService;
use Core\Routing\Router;

class HomeController extends BaseController
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
        // We inject legacy Router for now as it's used in view for URL generation 
        // until we refactor URL generation to not rely on it.
        // Actually, existing view uses $router->getLocale() and $router->url(). 
        // We need to keep passing it to the view.
        Router $router 
    ) {
        parent::__construct($authService, $keyValidator, $request, $response);
        $this->articleService = $articleService;
        $this->productRepository = $productRepository;
        $this->translator = Translator::getInstance();
        $this->router = $router;
    }

    public function index(): void
    {
        // Replicating logic from index.php: 'home' handler
        // $articles = $articleService->listArticles(6, 0, $router->getLocale());
        // $products = $productRepository->findAll(8, 0, $router->getLocale());
        
        $locale = $this->router->getLocale();
        
        $articles = $this->articleService->listArticles(6, 0, $locale);
        $products = $this->productRepository->findAll(8, 0, $locale);
            
        // Use 'home' view which resolves to public/templates/home.php
        $this->response->view('home', [
            'articles' => $articles,
            'products' => $products,
            'translator' => $this->translator,
            'router' => $this->router,
            'pageTitle' => 'Welcome to TuzyCMS'
        ]);
    }
}
