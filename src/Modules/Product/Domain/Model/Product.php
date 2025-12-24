<?php

declare(strict_types=1);

namespace Modules\Product\Domain\Model;

use DateTimeImmutable;

class Product
{
    private ?int $id = null;
    private string $name;
    private string $slug;
    private string $description;
    private string $shortDescription;
    private int $categoryId;
    private float $oldPrice;
    private float $newPrice;
    private ?float $promotionalPrice = null;
    private string $sku;
    private int $stock = 0;
    private string $status = 'draft';
    private ?string $featuredImage = null;
    private array $images = [];
    private ?string $metaTitle = null;
    private ?string $metaDescription = null;
    private ?string $metaKeywords = null;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;
    private int $views = 0;
    private int $sales = 0;

    public function __construct(
        string $name,
        string $slug,
        string $description,
        string $shortDescription,
        int $categoryId,
        float $oldPrice,
        float $newPrice,
        string $sku
    ) {
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->shortDescription = $shortDescription;
        $this->categoryId = $categoryId;
        $this->oldPrice = $oldPrice;
        $this->newPrice = $newPrice;
        $this->sku = $sku;
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): void
    {
        $this->shortDescription = $shortDescription;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function setCategoryId(int $categoryId): void
    {
        $this->categoryId = $categoryId;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getOldPrice(): float
    {
        return $this->oldPrice;
    }

    public function setOldPrice(float $oldPrice): void
    {
        $this->oldPrice = $oldPrice;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getNewPrice(): float
    {
        return $this->newPrice;
    }

    public function setNewPrice(float $newPrice): void
    {
        $this->newPrice = $newPrice;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getPromotionalPrice(): ?float
    {
        return $this->promotionalPrice;
    }

    public function setPromotionalPrice(?float $promotionalPrice): void
    {
        $this->promotionalPrice = $promotionalPrice;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getFinalPrice(): float
    {
        return $this->promotionalPrice ?? $this->newPrice;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function setSku(string $sku): void
    {
        $this->sku = $sku;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): void
    {
        $this->stock = $stock;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function reduceStock(int $quantity): void
    {
        if ($this->stock < $quantity) {
            throw new \RuntimeException('Insufficient stock.');
        }
        $this->stock -= $quantity;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getFeaturedImage(): ?string
    {
        return $this->featuredImage;
    }

    public function setFeaturedImage(?string $featuredImage): void
    {
        $this->featuredImage = $featuredImage;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function setImages(array $images): void
    {
        $this->images = $images;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): void
    {
        $this->metaTitle = $metaTitle;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(?string $metaKeywords): void
    {
        $this->metaKeywords = $metaKeywords;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function incrementViews(): void
    {
        $this->views++;
    }

    public function getSales(): int
    {
        return $this->sales;
    }

    public function incrementSales(int $quantity): void
    {
        $this->sales += $quantity;
    }
}

