<?php

declare(strict_types=1);

namespace Modules\Article\Infrastructure\Repository;

use Shared\Infrastructure\Database\DatabaseConnection;
use Shared\Infrastructure\Security\ContentEncryption;

class PageRepository
{
    private \PDO $db;
    private ContentEncryption $encryption;

    public function __construct(ContentEncryption $encryption)
    {
        $this->db = DatabaseConnection::getInstance();
        $this->encryption = $encryption;
    }

    public function findBySlug(string $slug, string $locale = 'vi'): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM pages WHERE slug = :slug AND locale = :locale LIMIT 1");
        $stmt->execute([':slug' => $slug, ':locale' => $locale]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        // Decrypt content if encrypted
        try {
            $data['content'] = $this->encryption->decrypt($data['content']);
        } catch (\Exception $e) {
            // Keep original if decryption fails (might not be encrypted)
        }

        return $data;
    }

    public function save(array $data): void
    {
        // Encrypt content before saving
        $data['content'] = $this->encryption->encrypt($data['content']);

        if (isset($data['id'])) {
            $this->update($data);
        } else {
            $this->insert($data);
        }
    }

    private function insert(array $data): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO pages (title, slug, content, meta_title, meta_description, meta_keywords, locale, created_at, updated_at)
            VALUES (:title, :slug, :content, :meta_title, :meta_description, :meta_keywords, :locale, NOW(), NOW())
        ");
        $stmt->execute([
            ':title' => $data['title'],
            ':slug' => $data['slug'],
            ':content' => $data['content'],
            ':meta_title' => $data['meta_title'] ?? '',
            ':meta_description' => $data['meta_description'] ?? '',
            ':meta_keywords' => $data['meta_keywords'] ?? '',
            ':locale' => $data['locale'] ?? 'vi'
        ]);
    }

    private function update(array $data): void
    {
        $stmt = $this->db->prepare("
            UPDATE pages SET 
                title = :title, 
                slug = :slug, 
                content = :content, 
                meta_title = :meta_title, 
                meta_description = :meta_description, 
                meta_keywords = :meta_keywords, 
                updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            ':id' => $data['id'],
            ':title' => $data['title'],
            ':slug' => $data['slug'],
            ':content' => $data['content'],
            ':meta_title' => $data['meta_title'] ?? '',
            ':meta_description' => $data['meta_description'] ?? '',
            ':meta_keywords' => $data['meta_keywords'] ?? ''
        ]);
    }
}
