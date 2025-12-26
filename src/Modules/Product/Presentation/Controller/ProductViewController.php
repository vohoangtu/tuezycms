<?php

declare(strict_types=1);

namespace Modules\Product\Presentation\Controller;

use Modules\Product\Infrastructure\Repository\ProductRepository;
use Shared\Infrastructure\Controller\BaseController;
use Shared\Infrastructure\I18n\Translator;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Modules\User\Application\Service\AuthService;
use Core\Routing\Router;
use Shared\Infrastructure\Exception\NotFoundException;

class ProductViewController extends BaseController
{
    private ProductRepository $productRepository;
    private Translator $translator;
    private Router $router;

    public function __construct(
        AuthService $authService,
        KeyValidator $keyValidator,
        Request $request,
        Response $response,
        ProductRepository $productRepository,
        Router $router
    ) {
        parent::__construct($authService, $keyValidator, $request, $response);
        $this->productRepository = $productRepository;
        $this->translator = Translator::getInstance();
        $this->router = $router;
    }

    public function index(): void
    {
        // Replicating logic from index.php: 'products' handler
        $products = $this->productRepository->findAll(20, 0, $this->router->getLocale());
            
        $this->response->view('products', [
            'products' => $products,
            'translator' => $this->translator,
            'router' => $this->router
        ]);
    }
}
