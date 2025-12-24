<?php

declare(strict_types=1);

namespace Modules\Media\Infrastructure\Repository;

use Modules\Media\Domain\Model\MediaFile;
use Modules\Media\Domain\Model\MediaType;
use Shared\Infrastructure\Database\DatabaseConnection;

class MediaRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function save(MediaFile $mediaFile): void
    {
        if ($mediaFile->getId() === null) {
            $this->insert($mediaFile);
        } else {
            $this->update($mediaFile);
        }
    }

    private function insert(MediaFile $mediaFile): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO media_files (
                filename, original_filename, path, type, mime_type, size,
                width, height, thumbnail_path, alt_text, description,
                created_by, created_at, updated_at
            ) VALUES (
                :filename, :original_filename, :path, :type, :mime_type, :size,
                :width, :height, :thumbnail_path, :alt_text, :description,
                :created_by, :created_at, :updated_at
            )
        ");

        $stmt->execute([
            ':filename' => $mediaFile->getFilename(),
            ':original_filename' => $mediaFile->getOriginalFilename(),
            ':path' => $mediaFile->getPath(),
            ':type' => $mediaFile->getType()->value,
            ':mime_type' => $mediaFile->getMimeType(),
            ':size' => $mediaFile->getSize(),
            ':width' => $mediaFile->getWidth(),
            ':height' => $mediaFile->getHeight(),
            ':thumbnail_path' => $mediaFile->getThumbnailPath(),
            ':alt_text' => $mediaFile->getAltText(),
            ':description' => $mediaFile->getDescription(),
            ':created_by' => $mediaFile->getCreatedBy(),
            ':created_at' => $mediaFile->getCreatedAt()->format('Y-m-d H:i:s'),
            ':updated_at' => $mediaFile->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $mediaFile->setId((int)$this->db->lastInsertId());
    }

    private function update(MediaFile $mediaFile): void
    {
        $stmt = $this->db->prepare("
            UPDATE media_files SET
                alt_text = :alt_text,
                description = :description,
                updated_at = :updated_at
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $mediaFile->getId(),
            ':alt_text' => $mediaFile->getAltText(),
            ':description' => $mediaFile->getDescription(),
            ':updated_at' => $mediaFile->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function findById(int $id): ?MediaFile
    {
        $stmt = $this->db->prepare("SELECT * FROM media_files WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }

    public function findAll(int $limit = 50, int $offset = 0, ?string $type = null, ?string $search = null): array
    {
        $where = [];
        $params = [];

        if ($type) {
            $where[] = 'type = :type';
            $params[':type'] = $type;
        }

        if ($search) {
            $where[] = '(filename LIKE :search OR original_filename LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("
            SELECT * FROM media_files
            {$whereClause}
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ");

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $files = [];
        while ($data = $stmt->fetch()) {
            $files[] = $this->mapToEntity($data);
        }

        return $files;
    }

    public function count(?string $type = null, ?string $search = null): int
    {
        $where = [];
        $params = [];

        if ($type) {
            $where[] = 'type = :type';
            $params[':type'] = $type;
        }

        if ($search) {
            $where[] = '(filename LIKE :search OR original_filename LIKE :search)';
            $params[':search'] = '%' . $search . '%';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM media_files {$whereClause}");
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();

        $result = $stmt->fetch();
        return (int)($result['count'] ?? 0);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM media_files WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public function getUsage(int $mediaId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM media_usage
            WHERE media_id = :media_id
        ");
        $stmt->execute([':media_id' => $mediaId]);
        
        return $stmt->fetchAll();
    }

    public function addUsage(int $mediaId, string $entityType, string $usageType, ?int $entityId = null, ?string $entityKey = null): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO media_usage (media_id, entity_type, entity_id, entity_key, usage_type)
            VALUES (:media_id, :entity_type, :entity_id, :entity_key, :usage_type)
        ");
        $stmt->execute([
            ':media_id' => $mediaId,
            ':entity_type' => $entityType,
            ':entity_id' => $entityId,
            ':entity_key' => $entityKey,
            ':usage_type' => $usageType,
        ]);
    }

    public function removeUsage(int $mediaId, string $entityType, string $usageType, ?int $entityId = null, ?string $entityKey = null): void
    {
        $stmt = $this->db->prepare("
            DELETE FROM media_usage
            WHERE media_id = :media_id
            AND entity_type = :entity_type
            AND usage_type = :usage_type
            AND (entity_id = :entity_id OR (:entity_id IS NULL AND entity_id IS NULL))
            AND (entity_key = :entity_key OR (:entity_key IS NULL AND entity_key IS NULL))
        ");
        $stmt->execute([
            ':media_id' => $mediaId,
            ':entity_type' => $entityType,
            ':entity_id' => $entityId,
            ':entity_key' => $entityKey,
        ]);
    }

    private function mapToEntity(array $data): MediaFile
    {
        $mediaFile = new MediaFile(
            $data['filename'],
            $data['original_filename'],
            $data['path'],
            MediaType::from($data['type']),
            $data['mime_type'],
            (int)$data['size']
        );

        $mediaFile->setId($data['id']);
        $mediaFile->setWidth($data['width']);
        $mediaFile->setHeight($data['height']);
        $mediaFile->setThumbnailPath($data['thumbnail_path']);
        $mediaFile->setAltText($data['alt_text']);
        $mediaFile->setDescription($data['description']);
        $mediaFile->setCreatedBy($data['created_by']);

        return $mediaFile;
    }
}

