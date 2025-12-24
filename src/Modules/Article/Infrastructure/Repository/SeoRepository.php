<?php
declare(strict_types=1);
namespace Modules\Article\Infrastructure\Repository;
use Shared\Infrastructure\Database\DatabaseConnection;
class SeoRepository {
    private \PDO $db;
    public function __construct() { $this->db = DatabaseConnection::getInstance(); }
    public function findByPath(string $path): ?array {
        $stmt = $this->db->prepare("SELECT * FROM seo_configs WHERE path = :path LIMIT 1");
        $stmt->execute([':path' => $path]);
        return $stmt->fetch() ?: null;
    }
}
