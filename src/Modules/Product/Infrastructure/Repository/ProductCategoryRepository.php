<?php

declare(strict_types=1);

namespace Modules\Product\Infrastructure\Repository;

use Modules\Product\Domain\Model\ProductCategory;
use Shared\Infrastructure\Database\DatabaseConnection;

class ProductCategoryRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function save(ProductCategory $category): void
    {
        if ($category->getId() === null) {
            $this->insert($category);
        } else {
            $this->update($category);
        }
    }

    private function insert(ProductCategory $category): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO product_categories (
                name, slug, description, parent_id, sort_order, is_active,
                image, meta_title, meta_description, meta_keywords
            ) VALUES (
                :name, :slug, :description, :parent_id, :sort_order, :is_active,
                :image, :meta_title, :meta_description, :meta_keywords
            )
        ");

        $stmt->execute([
            ':name' => $category->getName(),
            ':slug' => $category->getSlug(),
            ':description' => $category->getDescription(),
            ':parent_id' => $category->getParentId(),
            ':sort_order' => $category->getSortOrder(),
            ':is_active' => $category->isActive() ? 1 : 0,
            ':image' => $category->getImage(),
            ':meta_title' => $category->getMetaTitle(),
            ':meta_description' => $category->getMetaDescription(),
            ':meta_keywords' => $category->getMetaKeywords(),
        ]);

        $category->setId((int)$this->db->lastInsertId());
    }

    private function update(ProductCategory $category): void
    {
        $stmt = $this->db->prepare("
            UPDATE product_categories SET
                name = :name,
                slug = :slug,
                description = :description,
                parent_id = :parent_id,
                sort_order = :sort_order,
                is_active = :is_active,
                image = :image,
                meta_title = :meta_title,
                meta_description = :meta_description,
                meta_keywords = :meta_keywords
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $category->getId(),
            ':name' => $category->getName(),
            ':slug' => $category->getSlug(),
            ':description' => $category->getDescription(),
            ':parent_id' => $category->getParentId(),
            ':sort_order' => $category->getSortOrder(),
            ':is_active' => $category->isActive() ? 1 : 0,
            ':image' => $category->getImage(),
            ':meta_title' => $category->getMetaTitle(),
            ':meta_description' => $category->getMetaDescription(),
            ':meta_keywords' => $category->getMetaKeywords(),
        ]);
    }

    public function findById(int $id): ?ProductCategory
    {
        $stmt = $this->db->prepare("SELECT * FROM product_categories WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM product_categories ORDER BY sort_order, name");
        $categories = [];

        while ($data = $stmt->fetch()) {
            $categories[] = $this->mapToEntity($data);
        }

        return $categories;
    }

    public function findActive(): array
    {
        $stmt = $this->db->query("SELECT * FROM product_categories WHERE is_active = 1 ORDER BY sort_order, name");
        $categories = [];

        while ($data = $stmt->fetch()) {
            $categories[] = $this->mapToEntity($data);
        }

        return $categories;
    }

    private function mapToEntity(array $data): ProductCategory
    {
        $category = new ProductCategory(
            $data['name'],
            $data['slug'],
            $data['description']
        );

        $category->setId($data['id']);
        $category->setParentId($data['parent_id']);
        $category->setSortOrder($data['sort_order']);
        $category->setIsActive((bool)$data['is_active']);
        $category->setImage($data['image']);
        $category->setMetaTitle($data['meta_title']);
        $category->setMetaDescription($data['meta_description']);
        $category->setMetaKeywords($data['meta_keywords']);

        return $category;
    }
}

