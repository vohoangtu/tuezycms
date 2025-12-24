<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Database;

use PDO;

/**
 * Query Builder
 * Fluent interface for building SQL queries
 */
class QueryBuilder
{
    private PDO $pdo;
    private string $table;
    private array $selects = ['*'];
    private array $wheres = [];
    private array $joins = [];
    private array $orderBys = [];
    private array $groupBys = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private array $bindings = [];

    public function __construct(PDO $pdo, string $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    /**
     * Set columns to select
     */
    public function select(string ...$columns): self
    {
        $this->selects = $columns;
        return $this;
    }

    /**
     * Add WHERE clause
     */
    public function where(string $column, string $operator, mixed $value): self
    {
        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'AND'
        ];
        return $this;
    }

    /**
     * Add OR WHERE clause
     */
    public function orWhere(string $column, string $operator, mixed $value): self
    {
        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'OR'
        ];
        return $this;
    }

    /**
     * Add WHERE IN clause
     */
    public function whereIn(string $column, array $values): self
    {
        $this->wheres[] = [
            'type' => 'in',
            'column' => $column,
            'values' => $values,
            'boolean' => 'AND'
        ];
        return $this;
    }

    /**
     * Add JOIN clause
     */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = [
            'type' => $type,
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];
        return $this;
    }

    /**
     * Add LEFT JOIN clause
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    /**
     * Add ORDER BY clause
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBys[] = [
            'column' => $column,
            'direction' => strtoupper($direction)
        ];
        return $this;
    }

    /**
     * Add GROUP BY clause
     */
    public function groupBy(string ...$columns): self
    {
        $this->groupBys = array_merge($this->groupBys, $columns);
        return $this;
    }

    /**
     * Set LIMIT
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set OFFSET
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Execute query and get all results
     */
    public function get(): array
    {
        $sql = $this->toSql();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get first result
     */
    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Find by ID
     */
    public function find(int $id): ?array
    {
        return $this->where('id', '=', $id)->first();
    }

    /**
     * Insert data
     */
    public function insert(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));
        
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Update data
     */
    public function update(array $data): int
    {
        $sets = [];
        $bindings = [];
        
        foreach ($data as $column => $value) {
            $sets[] = "{$column} = ?";
            $bindings[] = $value;
        }
        
        $sql = sprintf(
            "UPDATE %s SET %s%s",
            $this->table,
            implode(', ', $sets),
            $this->compileWheres()
        );
        
        $bindings = array_merge($bindings, $this->bindings);
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        
        return $stmt->rowCount();
    }

    /**
     * Delete data
     */
    public function delete(): int
    {
        $sql = sprintf(
            "DELETE FROM %s%s",
            $this->table,
            $this->compileWheres()
        );
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        
        return $stmt->rowCount();
    }

    /**
     * Count rows
     */
    public function count(string $column = '*'): int
    {
        return (int) $this->aggregate('COUNT', $column);
    }

    /**
     * Sum column
     */
    public function sum(string $column): float
    {
        return (float) $this->aggregate('SUM', $column);
    }

    /**
     * Average column
     */
    public function avg(string $column): float
    {
        return (float) $this->aggregate('AVG', $column);
    }

    /**
     * Min value
     */
    public function min(string $column): mixed
    {
        return $this->aggregate('MIN', $column);
    }

    /**
     * Max value
     */
    public function max(string $column): mixed
    {
        return $this->aggregate('MAX', $column);
    }

    /**
     * Execute aggregate function
     */
    private function aggregate(string $function, string $column): mixed
    {
        $sql = sprintf(
            "SELECT %s(%s) as aggregate FROM %s%s%s",
            $function,
            $column,
            $this->table,
            $this->compileJoins(),
            $this->compileWheres()
        );
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['aggregate'] ?? 0;
    }

    /**
     * Build SELECT SQL
     */
    private function toSql(): string
    {
        $sql = sprintf(
            "SELECT %s FROM %s%s%s%s%s%s",
            implode(', ', $this->selects),
            $this->table,
            $this->compileJoins(),
            $this->compileWheres(),
            $this->compileGroupBys(),
            $this->compileOrderBys(),
            $this->compileLimitOffset()
        );
        
        return $sql;
    }

    /**
     * Compile JOIN clauses
     */
    private function compileJoins(): string
    {
        if (empty($this->joins)) {
            return '';
        }
        
        $sql = '';
        foreach ($this->joins as $join) {
            $sql .= sprintf(
                " %s JOIN %s ON %s %s %s",
                $join['type'],
                $join['table'],
                $join['first'],
                $join['operator'],
                $join['second']
            );
        }
        
        return $sql;
    }

    /**
     * Compile WHERE clauses
     */
    private function compileWheres(): string
    {
        if (empty($this->wheres)) {
            return '';
        }
        
        $sql = ' WHERE ';
        $first = true;
        
        foreach ($this->wheres as $where) {
            if (!$first) {
                $sql .= " {$where['boolean']} ";
            }
            
            if ($where['type'] === 'basic') {
                $sql .= "{$where['column']} {$where['operator']} ?";
                $this->bindings[] = $where['value'];
            } elseif ($where['type'] === 'in') {
                $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
                $sql .= "{$where['column']} IN ({$placeholders})";
                $this->bindings = array_merge($this->bindings, $where['values']);
            }
            
            $first = false;
        }
        
        return $sql;
    }

    /**
     * Compile GROUP BY clause
     */
    private function compileGroupBys(): string
    {
        if (empty($this->groupBys)) {
            return '';
        }
        
        return ' GROUP BY ' . implode(', ', $this->groupBys);
    }

    /**
     * Compile ORDER BY clause
     */
    private function compileOrderBys(): string
    {
        if (empty($this->orderBys)) {
            return '';
        }
        
        $orders = array_map(function($order) {
            return "{$order['column']} {$order['direction']}";
        }, $this->orderBys);
        
        return ' ORDER BY ' . implode(', ', $orders);
    }

    /**
     * Compile LIMIT and OFFSET
     */
    private function compileLimitOffset(): string
    {
        $sql = '';
        
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }
        
        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }
        
        return $sql;
    }
}
