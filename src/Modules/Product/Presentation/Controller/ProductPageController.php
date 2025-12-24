<?php

declare(strict_types=1);

namespace Modules\Product\Presentation\Controller;

use Modules\User\Application\Service\AuthService;
use Modules\Product\Application\Service\ProductService;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Controller\BaseController;

class ProductPageController extends BaseController
{
    private ProductService $productService;

    public function __construct(
        AuthService $authService,
        KeyValidator $keyValidator,
        Request $request,
        Response $response,
        ProductService $productService
    ) {
        parent::__construct($authService, $keyValidator, $request, $response);
        $this->productService = $productService;
    }

    /**
     * Show products page
     */
    public function index(): void
    {
        $products = $this->productService->listProducts();
        $categories = $this->productService->listActiveCategories();

        $this->render('admin/products', [
            'products' => $products,
            'categories' => $categories
        ]);
    }
}
