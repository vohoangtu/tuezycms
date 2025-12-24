<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Database;

use PDO;
use PDOException;
use Shared\Infrastructure\Config\AppConfig;

class DatabaseConnection
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = AppConfig::getInstance();
            
            $host = $config->get('database.host');
            $dbname = $config->get('database.dbname');
            $username = $config->get('database.username');
            $password = $config->get('database.password');
            $charset = $config->get('database.charset');

            $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

            try {
                self::$instance = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
