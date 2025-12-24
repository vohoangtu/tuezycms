<?php

declare(strict_types=1);

namespace Modules\Promotion\Application\Service;

use TuzyCMS\Domain\Promotion\Promotion;
use TuzyCMS\Domain\Promotion\PromotionType;
use Modules\Promotion\Infrastructure\Repository\PromotionRepository;

class PromotionService
{
    private PromotionRepository $promotionRepository;

    public function __construct(PromotionRepository $promotionRepository)
    {
        $this->promotionRepository = $promotionRepository;
    }

    /**
     * Create promotion
     */
    public function createPromotion(
        string $name,
        string $code,
        string $type,
        float $value,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        ?float $minOrderAmount = null,
        ?float $maxDiscountAmount = null,
        ?int $productId = null,
        ?int $categoryId = null,
        int $usageLimit = 0
    ): Promotion {
        $promotionType = PromotionType::from($type);
        
        $promotion = new Promotion(
            $name,
            $code,
            $promotionType,
            $value,
            $startDate,
            $endDate
        );

        if ($minOrderAmount !== null) {
            $promotion->setMinOrderAmount($minOrderAmount);
        }
        if ($maxDiscountAmount !== null) {
            $promotion->setMaxDiscountAmount($maxDiscountAmount);
        }
        if ($productId !== null) {
            $promotion->setProductId($productId);
        }
        if ($categoryId !== null) {
            $promotion->setCategoryId($categoryId);
        }
        if ($usageLimit > 0) {
            $promotion->setUsageLimit($usageLimit);
        }

        $this->promotionRepository->save($promotion);

        return $promotion;
    }

    /**
     * Update promotion
     */
    public function updatePromotion(
        int $id,
        string $name,
        string $code,
        string $type,
        float $value,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate,
        ?float $minOrderAmount = null,
        ?float $maxDiscountAmount = null,
        ?int $productId = null,
        ?int $categoryId = null,
        int $usageLimit = 0,
        bool $isActive = true
    ): Promotion {
        $promotion = $this->promotionRepository->findById($id);
        if ($promotion === null) {
            throw new \RuntimeException('Promotion not found.');
        }

        $promotionType = PromotionType::from($type);

        $promotion->setName($name);
        $promotion->setCode($code);
        $promotion->setType($promotionType);
        $promotion->setValue($value);
        $promotion->setStartDate($startDate);
        $promotion->setEndDate($endDate);
        $promotion->setMinOrderAmount($minOrderAmount);
        $promotion->setMaxDiscountAmount($maxDiscountAmount);
        $promotion->setProductId($productId);
        $promotion->setCategoryId($categoryId);
        $promotion->setUsageLimit($usageLimit);
        $promotion->setIsActive($isActive);

        $this->promotionRepository->save($promotion);

        return $promotion;
    }

    /**
     * Get promotion by ID
     */
    public function getPromotion(int $id): ?Promotion
    {
        return $this->promotionRepository->findById($id);
    }

    /**
     * Get promotion by code
     */
    public function getPromotionByCode(string $code): ?Promotion
    {
        return $this->promotionRepository->findByCode($code);
    }

    /**
     * Validate promotion code
     */
    public function validatePromotionCode(string $code, float $orderAmount, ?int $productId = null, ?int $categoryId = null): ?Promotion
    {
        $promotion = $this->promotionRepository->findByCode($code);
        
        if ($promotion === null) {
            return null;
        }

        if (!$promotion->isValid(new \DateTimeImmutable())) {
            return null;
        }

        // Check if promotion applies to specific product/category
        if ($promotion->getProductId() !== null && $promotion->getProductId() !== $productId) {
            return null;
        }

        if ($promotion->getCategoryId() !== null && $promotion->getCategoryId() !== $categoryId) {
            return null;
        }

        // Check minimum order amount
        if ($promotion->getMinOrderAmount() !== null && $orderAmount < $promotion->getMinOrderAmount()) {
            return null;
        }

        return $promotion;
    }

    /**
     * List all promotions
     */
    public function listPromotions(int $limit = 100, int $offset = 0, ?bool $activeOnly = null): array
    {
        return $this->promotionRepository->findAll($limit, $offset, $activeOnly);
    }

    /**
     * Delete promotion
     */
    public function deletePromotion(int $id): void
    {
        $promotion = $this->promotionRepository->findById($id);
        if ($promotion === null) {
            throw new \RuntimeException('Promotion not found.');
        }

        $this->promotionRepository->delete($id);
    }
}

