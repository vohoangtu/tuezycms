<?php

declare(strict_types=1);

namespace Modules\Product\Presentation\Controller;

use Modules\User\Application\Service\AuthService;
use Modules\Product\Application\Service\ProductService;
use Shared\Infrastructure\Exception\NotFoundException;
use Shared\Infrastructure\Exception\BadRequestException;
use Shared\Infrastructure\Http\Request;
use Shared\Infrastructure\Http\Response;
use Shared\Infrastructure\Security\KeyValidator;
use Shared\Infrastructure\Controller\BaseController;

class ProductController extends BaseController
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
     * List products (API)
     */
    public function index(): void
    {
        if ($this->request->method() !== 'GET') {
            throw new BadRequestException('Method not allowed');
        }

        $products = $this->productService->listProducts();
        $this->json($products);
    }

    /**
     * Get product by ID (API)
     */
    public function show(int $id): void
    {
        if ($this->request->method() !== 'GET') {
            throw new BadRequestException('Method not allowed');
        }

        $product = $this->productService->getProduct($id);
        
        if ($product === null) {
            throw new NotFoundException('Product not found');
        }

        $this->json([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'slug' => $product->getSlug(),
            'description' => $product->getDescription(),
            'short_description' => $product->getShortDescription(),
            'category_id' => $product->getCategoryId(),
            'old_price' => $product->getOldPrice(),
            'new_price' => $product->getNewPrice(),
            'promotional_price' => $product->getPromotionalPrice(),
            'sku' => $product->getSku(),
            'stock' => $product->getStock(),
            'status' => $product->getStatus(),
        ]);
    }

    /**
     * Create or update product (API)
     */
    public function store(): void
    {
        if ($this->request->method() !== 'POST') {
            throw new BadRequestException('Method not allowed');
        }

        $data = $this->request->input();

        if (isset($data['id']) && $data['id']) {
            $product = $this->productService->updateProduct(
                (int)$data['id'],
                $data['name'],
                $data['slug'],
                $data['description'],
                $data['short_description'] ?? '',
                (int)$data['category_id'],
                (float)$data['old_price'],
                (float)$data['new_price'],
                isset($data['promotional_price']) && $data['promotional_price'] ? (float)$data['promotional_price'] : null,
                $data['sku'],
                (int)$data['stock'],
                $data['status'] ?? 'draft'
            );
        } else {
            $product = $this->productService->createProduct(
                $data['name'],
                $data['slug'],
                $data['description'],
                $data['short_description'] ?? '',
                (int)$data['category_id'],
                (float)$data['old_price'],
                (float)$data['new_price'],
                $data['sku'],
                (int)($data['stock'] ?? 0)
            );
        }

        $this->json(['success' => true, 'id' => $product->getId()]);
    }

    /**
     * Delete product (API)
     */
    public function delete(): void
    {
        if ($this->request->method() !== 'DELETE') {
            throw new BadRequestException('Method not allowed');
        }

        $data = $this->request->input();
        $id = (int)($data['id'] ?? 0);

        if ($id <= 0) {
            throw new BadRequestException('Invalid product ID');
        }

        $product = $this->productService->getProduct($id);
        if ($product === null) {
            throw new NotFoundException('Product not found');
        }

        // Note: Add delete method to ProductService if needed
        throw new BadRequestException('Delete not implemented');
    }
}
