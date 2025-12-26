<?php

declare(strict_types=1);

namespace Modules\Security\Infrastructure\Repository;

use Modules\Security\Domain\Entity\SecurityLog;
use Modules\Security\Domain\Repository\SecurityLogRepositoryInterface;
use Shared\Infrastructure\Database\DatabaseConnection;

class DatabaseSecurityLogRepository implements SecurityLogRepositoryInterface
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function save(SecurityLog $log): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO security_logs (
                user_id, action, description, ip_address, user_agent, context, level, created_at
            ) VALUES (
                :user_id, :action, :description, :ip_address, :user_agent, :context, :level, :created_at
            )
        ");

        $stmt->execute([
            ':user_id' => $log->getUserId(),
            ':action' => $log->getAction(),
            ':description' => $log->getDescription(),
            ':ip_address' => $log->getIpAddress(),
            ':user_agent' => $log->getUserAgent(),
            ':context' => $log->getContext() ? json_encode($log->getContext()) : null,
            ':level' => $log->getLevel(),
            ':created_at' => $log->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);

        if ($log->getId() === null) {
            $log->setId((int)$this->db->lastInsertId());
        }
    }

    public function findAll(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $query = "SELECT * FROM security_logs WHERE 1=1";
        $params = [];

        if (isset($filters['user_id'])) {
            $query .= " AND user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        if (isset($filters['action'])) {
            $query .= " AND action = :action";
            $params[':action'] = $filters['action'];
        }
        if (isset($filters['ip_address'])) {
            $query .= " AND ip_address = :ip_address";
            $params[':ip_address'] = $filters['ip_address'];
        }
        if (isset($filters['level'])) {
            $query .= " AND level = :level";
            $params[':level'] = $filters['level'];
        }

        $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        
        $stmt->execute();
        
        $logs = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $logs[] = $this->mapToEntity($row);
        }
        
        return $logs;
    }

    public function count(array $filters = []): int
    {
        $query = "SELECT COUNT(*) FROM security_logs WHERE 1=1";
        $params = [];

        if (isset($filters['user_id'])) {
            $query .= " AND user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        if (isset($filters['action'])) {
            $query .= " AND action = :action";
            $params[':action'] = $filters['action'];
        }
        if (isset($filters['ip_address'])) {
            $query .= " AND ip_address = :ip_address";
            $params[':ip_address'] = $filters['ip_address'];
        }
        if (isset($filters['level'])) {
            $query .= " AND level = :level";
            $params[':level'] = $filters['level'];
        }
        if (isset($filters['from_date'])) {
            $query .= " AND created_at >= :from_date";
            $params[':from_date'] = $filters['from_date'];
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        
        return (int)$stmt->fetchColumn();
    }

    private function mapToEntity(array $row): SecurityLog
    {
        $log = new SecurityLog(
            $row['action'],
            $row['ip_address'],
            $row['user_id'] ? (int)$row['user_id'] : null,
            $row['description'],
            $row['user_agent'],
            $row['context'] ? json_decode($row['context'], true) : [],
            $row['level'] ?? 'info'
        );
        
        $log->setId((int)$row['id']);
        $log->setCreatedAt(new \DateTimeImmutable($row['created_at']));
        
        return $log;
    }
}
