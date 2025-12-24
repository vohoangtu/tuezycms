<?php

declare(strict_types=1);

namespace Modules\Promotion\Infrastructure\Repository;

use Modules\Promotion\Domain\Model\Promotion;
use Modules\Promotion\Domain\Model\PromotionType;
use Shared\Infrastructure\Database\DatabaseConnection;

class PromotionRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function save(Promotion $promotion): void
    {
        if ($promotion->getId() === null) {
            $this->insert($promotion);
        } else {
            $this->update($promotion);
        }
    }

    private function insert(Promotion $promotion): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO promotions (
                name, code, type, value, min_order_amount, max_discount_amount,
                product_id, category_id, start_date, end_date,
                usage_limit, used_count, is_active, created_at, updated_at
            ) VALUES (
                :name, :code, :type, :value, :min_order_amount, :max_discount_amount,
                :product_id, :category_id, :start_date, :end_date,
                :usage_limit, :used_count, :is_active, :created_at, :updated_at
            )
        ");

        $stmt->execute([
            ':name' => $promotion->getName(),
            ':code' => $promotion->getCode(),
            ':type' => $promotion->getType()->value,
            ':value' => $promotion->getValue(),
            ':min_order_amount' => $promotion->getMinOrderAmount(),
            ':max_discount_amount' => $promotion->getMaxDiscountAmount(),
            ':product_id' => $promotion->getProductId(),
            ':category_id' => $promotion->getCategoryId(),
            ':start_date' => $promotion->getStartDate()->format('Y-m-d H:i:s'),
            ':end_date' => $promotion->getEndDate()->format('Y-m-d H:i:s'),
            ':usage_limit' => $promotion->getUsageLimit(),
            ':used_count' => $promotion->getUsedCount(),
            ':is_active' => $promotion->isActive() ? 1 : 0,
            ':created_at' => $promotion->getCreatedAt()->format('Y-m-d H:i:s'),
            ':updated_at' => $promotion->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $promotion->setId((int)$this->db->lastInsertId());
    }

    private function update(Promotion $promotion): void
    {
        $stmt = $this->db->prepare("
            UPDATE promotions SET
                name = :name,
                code = :code,
                type = :type,
                value = :value,
                min_order_amount = :min_order_amount,
                max_discount_amount = :max_discount_amount,
                product_id = :product_id,
                category_id = :category_id,
                start_date = :start_date,
                end_date = :end_date,
                usage_limit = :usage_limit,
                used_count = :used_count,
                is_active = :is_active,
                updated_at = :updated_at
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $promotion->getId(),
            ':name' => $promotion->getName(),
            ':code' => $promotion->getCode(),
            ':type' => $promotion->getType()->value,
            ':value' => $promotion->getValue(),
            ':min_order_amount' => $promotion->getMinOrderAmount(),
            ':max_discount_amount' => $promotion->getMaxDiscountAmount(),
            ':product_id' => $promotion->getProductId(),
            ':category_id' => $promotion->getCategoryId(),
            ':start_date' => $promotion->getStartDate()->format('Y-m-d H:i:s'),
            ':end_date' => $promotion->getEndDate()->format('Y-m-d H:i:s'),
            ':usage_limit' => $promotion->getUsageLimit(),
            ':used_count' => $promotion->getUsedCount(),
            ':is_active' => $promotion->isActive() ? 1 : 0,
            ':updated_at' => $promotion->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function findById(int $id): ?Promotion
    {
        $stmt = $this->db->prepare("SELECT * FROM promotions WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }

    public function findByCode(string $code): ?Promotion
    {
        $stmt = $this->db->prepare("SELECT * FROM promotions WHERE code = :code");
        $stmt->execute([':code' => $code]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }

    public function findAll(int $limit = 100, int $offset = 0, ?bool $activeOnly = null): array
    {
        $sql = "SELECT * FROM promotions";
        $params = [];

        if ($activeOnly !== null) {
            $sql .= " WHERE is_active = :is_active";
            $params[':is_active'] = $activeOnly ? 1 : 0;
        }

        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->execute();

        $promotions = [];
        while ($data = $stmt->fetch()) {
            $promotions[] = $this->mapToEntity($data);
        }

        return $promotions;
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM promotions WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    private function mapToEntity(array $data): Promotion
    {
        $type = PromotionType::from($data['type']);

        $promotion = new Promotion(
            $data['name'],
            $data['code'],
            $type,
            (float)$data['value'],
            new \DateTimeImmutable($data['start_date']),
            new \DateTimeImmutable($data['end_date'])
        );

        $promotion->setId($data['id']);
        $promotion->setMinOrderAmount($data['min_order_amount'] ? (float)$data['min_order_amount'] : null);
        $promotion->setMaxDiscountAmount($data['max_discount_amount'] ? (float)$data['max_discount_amount'] : null);
        $promotion->setProductId($data['product_id']);
        $promotion->setCategoryId($data['category_id']);
        $promotion->setUsageLimit($data['usage_limit']);
        $promotion->setUsedCount($data['used_count']);
        $promotion->setIsActive((bool)$data['is_active']);

        return $promotion;
    }
}

