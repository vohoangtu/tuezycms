<?php

declare(strict_types=1);

namespace Modules\Article\Infrastructure\Repository;

use Modules\Article\Domain\Model\ArticleType;
use Shared\Infrastructure\Database\DatabaseConnection;

class ArticleTypeRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function save(ArticleType $type): void
    {
        if ($type->getId() === 0) {
            $this->insert($type);
        } else {
            $this->update($type);
        }
    }

    private function insert(ArticleType $type): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO article_types (name, slug, description, is_active)
            VALUES (:name, :slug, :description, :is_active)
        ");

        $stmt->execute([
            ':name' => $type->getName(),
            ':slug' => $type->getSlug(),
            ':description' => $type->getDescription(),
            ':is_active' => $type->isActive() ? 1 : 0,
        ]);

        $type->setId((int)$this->db->lastInsertId());
    }

    private function update(ArticleType $type): void
    {
        $stmt = $this->db->prepare("
            UPDATE article_types SET
                name = :name,
                slug = :slug,
                description = :description,
                is_active = :is_active
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $type->getId(),
            ':name' => $type->getName(),
            ':slug' => $type->getSlug(),
            ':description' => $type->getDescription(),
            ':is_active' => $type->isActive() ? 1 : 0,
        ]);
    }

    public function findById(int $id): ?ArticleType
    {
        $stmt = $this->db->prepare("SELECT * FROM article_types WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM article_types ORDER BY name");
        $types = [];

        while ($data = $stmt->fetch()) {
            $types[] = $this->mapToEntity($data);
        }

        return $types;
    }

    public function findActive(): array
    {
        $stmt = $this->db->query("SELECT * FROM article_types WHERE is_active = 1 ORDER BY name");
        $types = [];

        while ($data = $stmt->fetch()) {
            $types[] = $this->mapToEntity($data);
        }

        return $types;
    }

    private function mapToEntity(array $data): ArticleType
    {
        $type = new ArticleType(
            $data['name'],
            $data['slug'],
            $data['description'] ?? '',
            (bool)$data['is_active']
        );
        $type->setId($data['id']);
        return $type;
    }
}

