<?php

declare(strict_types=1);

namespace Modules\Promotion\Domain\Model;

use DateTimeImmutable;

class Promotion
{
    private ?int $id = null;
    private string $name;
    private string $code;
    private PromotionType $type;
    private float $value;
    private ?float $minOrderAmount = null;
    private ?float $maxDiscountAmount = null;
    private ?int $productId = null;
    private ?int $categoryId = null;
    private DateTimeImmutable $startDate;
    private DateTimeImmutable $endDate;
    private int $usageLimit = 0;
    private int $usedCount = 0;
    private bool $isActive = true;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $name,
        string $code,
        PromotionType $type,
        float $value,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ) {
        $this->name = $name;
        $this->code = $code;
        $this->type = $type;
        $this->value = $value;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getType(): PromotionType
    {
        return $this->type;
    }

    public function setType(PromotionType $type): void
    {
        $this->type = $type;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(float $value): void
    {
        $this->value = $value;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getMinOrderAmount(): ?float
    {
        return $this->minOrderAmount;
    }

    public function setMinOrderAmount(?float $minOrderAmount): void
    {
        $this->minOrderAmount = $minOrderAmount;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getMaxDiscountAmount(): ?float
    {
        return $this->maxDiscountAmount;
    }

    public function setMaxDiscountAmount(?float $maxDiscountAmount): void
    {
        $this->maxDiscountAmount = $maxDiscountAmount;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getProductId(): ?int
    {
        return $this->productId;
    }

    public function setProductId(?int $productId): void
    {
        $this->productId = $productId;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function setCategoryId(?int $categoryId): void
    {
        $this->categoryId = $categoryId;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeImmutable $startDate): void
    {
        $this->startDate = $startDate;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(DateTimeImmutable $endDate): void
    {
        $this->endDate = $endDate;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getUsageLimit(): int
    {
        return $this->usageLimit;
    }

    public function setUsageLimit(int $usageLimit): void
    {
        $this->usageLimit = $usageLimit;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getUsedCount(): int
    {
        return $this->usedCount;
    }

    public function incrementUsedCount(): void
    {
        $this->usedCount++;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isValid(DateTimeImmutable $now): bool
    {
        if (!$this->isActive) {
            return false;
        }

        if ($now < $this->startDate || $now > $this->endDate) {
            return false;
        }

        if ($this->usageLimit > 0 && $this->usedCount >= $this->usageLimit) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $orderAmount): float
    {
        if (!$this->isValid(new DateTimeImmutable())) {
            return 0.0;
        }

        if ($this->minOrderAmount !== null && $orderAmount < $this->minOrderAmount) {
            return 0.0;
        }

        $discount = match ($this->type) {
            PromotionType::PERCENTAGE => $orderAmount * ($this->value / 100),
            PromotionType::FIXED => $this->value,
            PromotionType::EVENT => $this->value, // Event promotions can be handled similarly
            default => 0.0,
        };

        if ($this->maxDiscountAmount !== null && $discount > $this->maxDiscountAmount) {
            $discount = $this->maxDiscountAmount;
        }

        return $discount;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}

