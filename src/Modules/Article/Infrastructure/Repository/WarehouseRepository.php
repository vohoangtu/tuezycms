<?php
declare(strict_types=1);
namespace Modules\Article\Infrastructure\Repository;
use Shared\Infrastructure\Database\DatabaseConnection;
class WarehouseRepository {
    private \PDO $db;
    public function __construct() { $this->db = DatabaseConnection::getInstance(); }
    public function findAll(): array { return $this->db->query("SELECT * FROM warehouse_stocks")->fetchAll(); }
}
