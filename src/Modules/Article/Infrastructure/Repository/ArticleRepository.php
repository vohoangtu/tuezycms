<?php

declare(strict_types=1);

namespace Modules\Article\Infrastructure\Repository;

use Modules\Article\Domain\Model\Article;
use Modules\Article\Domain\Model\ArticleType;
use Shared\Infrastructure\Database\DatabaseConnection;
use Shared\Infrastructure\Security\ContentEncryption;

class ArticleRepository
{
    private \PDO $db;
    private ContentEncryption $encryption;

    public function __construct(ContentEncryption $encryption)
    {
        $this->db = DatabaseConnection::getInstance();
        $this->encryption = $encryption;
    }

    public function save(Article $article): void
    {
        $encryptedContent = $this->encryption->encrypt($article->getContent());

        if ($article->getId() === null) {
            $this->insert($article, $encryptedContent);
        } else {
            $this->update($article, $encryptedContent);
        }
    }

    private function insert(Article $article, string $encryptedContent): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO articles (
                title, slug, content, type_id, status, featured_image,
                meta_title, meta_description, meta_keywords,
                author_id, views, locale, created_at, updated_at
            ) VALUES (
                :title, :slug, :content, :type_id, :status, :featured_image,
                :meta_title, :meta_description, :meta_keywords,
                :author_id, :views, :locale, :created_at, :updated_at
            )
        ");

        $stmt->execute([
            ':title' => $article->getTitle(),
            ':slug' => $article->getSlug(),
            ':content' => $encryptedContent,
            ':type_id' => $article->getType()->getId(),
            ':status' => $article->getStatus(),
            ':featured_image' => $article->getFeaturedImage(),
            ':meta_title' => $article->getMetaTitle(),
            ':meta_description' => $article->getMetaDescription(),
            ':meta_keywords' => $article->getMetaKeywords(),
            ':author_id' => $article->getAuthorId(),
            ':views' => $article->getViews(),
            ':locale' => 'vi',
            ':created_at' => $article->getCreatedAt()->format('Y-m-d H:i:s'),
            ':updated_at' => $article->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $article->setId((int)$this->db->lastInsertId());
    }

    private function update(Article $article, string $encryptedContent): void
    {
        $stmt = $this->db->prepare("
            UPDATE articles SET
                title = :title,
                slug = :slug,
                content = :content,
                type_id = :type_id,
                status = :status,
                featured_image = :featured_image,
                meta_title = :meta_title,
                meta_description = :meta_description,
                meta_keywords = :meta_keywords,
                views = :views,
                updated_at = :updated_at
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $article->getId(),
            ':title' => $article->getTitle(),
            ':slug' => $article->getSlug(),
            ':content' => $encryptedContent,
            ':type_id' => $article->getType()->getId(),
            ':status' => $article->getStatus(),
            ':featured_image' => $article->getFeaturedImage(),
            ':meta_title' => $article->getMetaTitle(),
            ':meta_description' => $article->getMetaDescription(),
            ':meta_keywords' => $article->getMetaKeywords(),
            ':views' => $article->getViews(),
            ':updated_at' => $article->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function findById(int $id): ?Article
    {
        $stmt = $this->db->prepare("
            SELECT a.*, at.id as type_id, at.name as type_name, at.slug as type_slug
            FROM articles a
            JOIN article_types at ON a.type_id = at.id
            WHERE a.id = :id
        ");

        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }

    public function findBySlug(string $slug, string $locale = 'vi'): ?Article
    {
        $stmt = $this->db->prepare("
            SELECT a.*, at.id as type_id, at.name as type_name, at.slug as type_slug
            FROM articles a
            JOIN article_types at ON a.type_id = at.id
            WHERE a.slug = :slug AND a.locale = :locale AND a.status = 'published'
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

    public function findAll(int $limit = 100, int $offset = 0, string $locale = 'vi'): array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, at.id as type_id, at.name as type_name, at.slug as type_slug
            FROM articles a
            JOIN article_types at ON a.type_id = at.id
            WHERE a.locale = :locale AND a.status = 'published'
            ORDER BY a.created_at DESC
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue(':locale', $locale, \PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $articles = [];
        while ($data = $stmt->fetch()) {
            $articles[] = $this->mapToEntity($data);
        }

        return $articles;
    }

    public function findByTypeSlug(string $typeSlug, int $limit = 100, int $offset = 0, string $locale = 'vi'): array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, at.id as type_id, at.name as type_name, at.slug as type_slug
            FROM articles a
            JOIN article_types at ON a.type_id = at.id
            WHERE at.slug = :type_slug AND a.locale = :locale AND a.status = 'published'
            ORDER BY a.created_at DESC
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue(':type_slug', $typeSlug, \PDO::PARAM_STR);
        $stmt->bindValue(':locale', $locale, \PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $articles = [];
        while ($data = $stmt->fetch()) {
            $articles[] = $this->mapToEntity($data);
        }

        return $articles;
    }

    public function findByType(int $typeId, int $limit = 100, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT a.*, at.id as type_id, at.name as type_name, at.slug as type_slug
            FROM articles a
            JOIN article_types at ON a.type_id = at.id
            WHERE a.type_id = :type_id AND a.status = 'published'
            ORDER BY a.created_at DESC
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindValue(':type_id', $typeId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $articles = [];
        while ($data = $stmt->fetch()) {
            $articles[] = $this->mapToEntity($data);
        }

        return $articles;
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM articles WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    private function mapToEntity(array $data): Article
    {
        $type = new ArticleType($data['type_name'], $data['type_slug']);
        $type->setId($data['type_id']);

        $article = new Article(
            $data['title'],
            $data['slug'],
            '',
            $type,
            $data['status']
        );

        $article->setId($data['id']);
        $featuredImage = $data['featured_image'];
        if (is_numeric($featuredImage)) {
            $article->setFeaturedImage((string)$featuredImage);
        } else {
            $article->setFeaturedImage($featuredImage);
        }
        $article->setMetaTitle($data['meta_title']);
        $article->setMetaDescription($data['meta_description']);
        $article->setMetaKeywords($data['meta_keywords']);
        $article->setAuthorId($data['author_id']);

        try {
            $decryptedContent = $this->encryption->decrypt($data['content']);
            $article->setContent($decryptedContent);
        } catch (\Exception $e) {
            $article->setContent('');
        }

        return $article;
    }
}
