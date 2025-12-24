<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Database;

use PDO;

/**
 * DB Facade
 * Static interface for database operations
 */
class DB
{
    /**
     * Create new query builder for table
     */
    public static function table(string $table): QueryBuilder
    {
        return new QueryBuilder(DatabaseConnection::getInstance(), $table);
    }

    /**
     * Begin transaction
     */
    public static function beginTransaction(): void
    {
        DatabaseConnection::getInstance()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public static function commit(): void
    {
        DatabaseConnection::getInstance()->commit();
    }

    /**
     * Rollback transaction
     */
    public static function rollBack(): void
    {
        DatabaseConnection::getInstance()->rollBack();
    }

    /**
     * Execute callback within transaction
     */
    public static function transaction(callable $callback): mixed
    {
        self::beginTransaction();
        
        try {
            $result = $callback();
            self::commit();
            return $result;
        } catch (\Exception $e) {
            self::rollBack();
            throw $e;
        }
    }

    /**
     * Get PDO instance
     */
    public static function connection(): PDO
    {
        return DatabaseConnection::getInstance();
    }
}
