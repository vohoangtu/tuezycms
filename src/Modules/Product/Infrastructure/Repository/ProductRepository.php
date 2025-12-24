<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Repository;

use Modules\Product\Domain\Model\Product;
use Modules\Product\Domain\Model\ProductCategory;
use Shared\Infrastructure\Database\DatabaseConnection;
use Shared\Infrastructure\Security\ContentEncryption;

class ProductRepository
{
    private \PDO $db;
    private ContentEncryption $encryption;

    public function __construct(ContentEncryption $encryption)
    {
        $this->db = DatabaseConnection::getInstance();
        $this->encryption = $encryption;
    }

    public function save(Product $product): void
    {
        $encryptedDescription = $this->encryption->encrypt($product->getDescription());
        $encryptedShortDescription = $this->encryption->encrypt($product->getShortDescription());

        if ($product->getId() === null) {
            $this->insert($product, $encryptedDescription, $encryptedShortDescription);
        } else {
            $this->update($product, $encryptedDescription, $encryptedShortDescription);
        }
    }

    private function insert(Product $product, string $encryptedDescription, string $encryptedShortDescription): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO products (
                name, slug, description, short_description, category_id,
                old_price, new_price, promotional_price, sku, stock, status,
                featured_image, images, meta_title, meta_description, meta_keywords,
                views, sales, locale, created_at, updated_at
            ) VALUES (
                :name, :slug, :description, :short_description, :category_id,
                :old_price, :new_price, :promotional_price, :sku, :stock, :status,
                :featured_image, :images, :meta_title, :meta_description, :meta_keywords,
                :views, :sales, :locale, :created_at, :updated_at
            )
        ");

        $stmt->execute([
            ':name' => $product->getName(),
            ':slug' => $product->getSlug(),
            ':description' => $encryptedDescription,
            ':short_description' => $encryptedShortDescription,
            ':category_id' => $product->getCategoryId(),
            ':old_price' => $product->getOldPrice(),
            ':new_price' => $product->getNewPrice(),
            ':promotional_price' => $product->getPromotionalPrice(),
            ':sku' => $product->getSku(),
            ':stock' => $product->getStock(),
            ':status' => $product->getStatus(),
            ':featured_image' => $product->getFeaturedImage(),
            ':images' => json_encode($product->getImages()),
            ':meta_title' => $product->getMetaTitle(),
            ':meta_description' => $product->getMetaDescription(),
            ':meta_keywords' => $product->getMetaKeywords(),
            ':views' => $product->getViews(),
            ':sales' => $product->getSales(),
            ':locale' => 'vi', // Default locale, should be passed from service
            ':created_at' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
            ':updated_at' => $product->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $product->setId((int)$this->db->lastInsertId());
    }

    private function update(Product $product, string $encryptedDescription, string $encryptedShortDescription): void
    {
        $stmt = $this->db->prepare("
            UPDATE products SET
                name = :name,
                slug = :slug,
                description = :description,
                short_description = :short_description,
                category_id = :category_id,
                old_price = :old_price,
                new_price = :new_price,
                promotional_price = :promotional_price,
                sku = :sku,
                stock = :stock,
                status = :status,
                featured_image = :featured_image,
                images = :images,
                meta_title = :meta_title,
                meta_description = :meta_description,
                meta_keywords = :meta_keywords,
                views = :views,
                sales = :sales,
                updated_at = :updated_at
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $product->getId(),
            ':name' => $product->getName(),
            ':slug' => $product->getSlug(),
            ':description' => $encryptedDescription,
            ':short_description' => $encryptedShortDescription,
            ':category_id' => $product->getCategoryId(),
            ':old_price' => $product->getOldPrice(),
            ':new_price' => $product->getNewPrice(),
            ':promotional_price' => $product->getPromotionalPrice(),
            ':sku' => $product->getSku(),
            ':stock' => $product->getStock(),
            ':status' => $product->getStatus(),
            ':featured_image' => $product->getFeaturedImage(),
            ':images' => json_encode($product->getImages()),
            ':meta_title' => $product->getMetaTitle(),
            ':meta_description' => $product->getMetaDescription(),
            ':meta_keywords' => $product->getMetaKeywords(),
            ':views' => $product->getViews(),
            ':sales' => $product->getSales(),
            ':updated_at' => $product->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function findById(int $id): ?Product
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }

    public function findBySlug(string $slug, string $locale = 'vi'): ?Product
    {
        $stmt = $this->db->prepare("
            SELECT * FROM products 
            WHERE slug = :slug AND locale = :locale AND status = 'published'
        ");
        $stmt->execute([
            ':slug' => $slug,
            ':locale' => $locale
        ]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }

    public function findByCategory(int $categoryId, int $limit = 100, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM products
            WHERE category_id = :category_id AND status = 'published'
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue(':category_id', $categoryId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $products = [];
        while ($data = $stmt->fetch()) {
            $products[] = $this->mapToEntity($data);
        }

        return $products;
    }

    public function findAll(int $limit = 100, int $offset = 0, string $locale = 'vi'): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM products
            WHERE locale = :locale AND status = 'published'
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue(':locale', $locale, \PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $products = [];
        while ($data = $stmt->fetch()) {
            $products[] = $this->mapToEntity($data);
        }

        return $products;
    }

    private function mapToEntity(array $data): Product
    {
        $product = new Product(
            $data['name'],
            $data['slug'],
            '', // Will be decrypted
            '', // Will be decrypted
            $data['category_id'],
            (float)$data['old_price'],
            (float)$data['new_price'],
            $data['sku']
        );

        $product->setId($data['id']);
        $product->setPromotionalPrice($data['promotional_price'] ? (float)$data['promotional_price'] : null);
        $product->setStock($data['stock']);
        $product->setStatus($data['status']);
        $product->setFeaturedImage($data['featured_image']);
        // Handle images - can be array of IDs or JSON string
        $images = $data['images'] ?? [];
        if (is_string($images)) {
            $images = json_decode($images, true) ?: [];
        }
        // Convert to array of integers (media IDs)
        $images = array_map('intval', is_array($images) ? $images : []);
        $product->setImages($images);
        $product->setMetaTitle($data['meta_title']);
        $product->setMetaDescription($data['meta_description']);
        $product->setMetaKeywords($data['meta_keywords']);

        // Decrypt content
        try {
            $decryptedDescription = $this->encryption->decrypt($data['description']);
            $product->setDescription($decryptedDescription);
        } catch (\Exception $e) {
            $product->setDescription('');
        }

        try {
            $decryptedShortDescription = $this->encryption->decrypt($data['short_description']);
            $product->setShortDescription($decryptedShortDescription);
        } catch (\Exception $e) {
            $product->setShortDescription('');
        }

        return $product;
    }
}

