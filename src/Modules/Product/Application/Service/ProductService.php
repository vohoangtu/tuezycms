<?php

declare(strict_types=1);

namespace Modules\Product\Application\Service;

use Modules\Product\Domain\Model\Product;
use Modules\Product\Domain\Model\ProductCategory;
use Modules\Product\Infrastructure\Repository\ProductRepository;
use Modules\Product\Infrastructure\Repository\ProductCategoryRepository;

class ProductService
{
    private ProductRepository $productRepository;
    private ProductCategoryRepository $categoryRepository;

    public function __construct(
        ProductRepository $productRepository,
        ProductCategoryRepository $categoryRepository
    ) {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function createProduct(
        string $name,
        string $slug,
        string $description,
        string $shortDescription,
        int $categoryId,
        float $oldPrice,
        float $newPrice,
        string $sku,
        int $stock = 0
    ): Product {
        $category = $this->categoryRepository->findById($categoryId);
        if ($category === null) {
            throw new \RuntimeException('Product category not found.');
        }

        $product = new Product(
            $name,
            $slug,
            $description,
            $shortDescription,
            $categoryId,
            $oldPrice,
            $newPrice,
            $sku
        );
        $product->setStock($stock);

        $this->productRepository->save($product);

        return $product;
    }

    public function updateProduct(
        int $id,
        string $name,
        string $slug,
        string $description,
        string $shortDescription,
        int $categoryId,
        float $oldPrice,
        float $newPrice,
        ?float $promotionalPrice,
        string $sku,
        int $stock,
        string $status
    ): Product {
        $product = $this->productRepository->findById($id);
        if ($product === null) {
            throw new \RuntimeException('Product not found.');
        }

        $product->setName($name);
        $product->setSlug($slug);
        $product->setDescription($description);
        $product->setShortDescription($shortDescription);
        $product->setCategoryId($categoryId);
        $product->setOldPrice($oldPrice);
        $product->setNewPrice($newPrice);
        $product->setPromotionalPrice($promotionalPrice);
        $product->setSku($sku);
        $product->setStock($stock);
        $product->setStatus($status);

        $this->productRepository->save($product);

        return $product;
    }

    public function getProduct(int $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    public function getProductBySlug(string $slug): ?Product
    {
        return $this->productRepository->findBySlug($slug);
    }

    public function listProducts(int $limit = 100, int $offset = 0): array
    {
        return $this->productRepository->findAll($limit, $offset);
    }

    public function listProductsByCategory(int $categoryId, int $limit = 100, int $offset = 0): array
    {
        return $this->productRepository->findByCategory($categoryId, $limit, $offset);
    }

    public function createCategory(
        string $name,
        string $slug,
        ?string $description = null,
        ?int $parentId = null
    ): ProductCategory {
        $category = new ProductCategory($name, $slug, $description);
        if ($parentId !== null) {
            $category->setParentId($parentId);
        }
        $this->categoryRepository->save($category);
        return $category;
    }

    public function listCategories(): array
    {
        return $this->categoryRepository->findAll();
    }

    public function listActiveCategories(): array
    {
        return $this->categoryRepository->findActive();
    }
}

