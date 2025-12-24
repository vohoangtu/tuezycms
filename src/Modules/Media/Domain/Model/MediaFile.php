<?php

declare(strict_types=1);

namespace Modules\Media\Domain\Model;

use DateTimeImmutable;

class MediaFile
{
    private ?int $id = null;
    private string $filename;
    private string $originalFilename;
    private string $path;
    private MediaType $type;
    private string $mimeType;
    private int $size;
    private ?int $width = null;
    private ?int $height = null;
    private ?string $thumbnailPath = null;
    private ?string $altText = null;
    private ?string $description = null;
    private ?int $createdBy = null;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $filename,
        string $originalFilename,
        string $path,
        MediaType $type,
        string $mimeType,
        int $size
    ) {
        $this->filename = $filename;
        $this->originalFilename = $originalFilename;
        $this->path = $path;
        $this->type = $type;
        $this->mimeType = $mimeType;
        $this->size = $size;
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

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getType(): MediaType
    {
        return $this->type;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): void
    {
        $this->width = $width;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): void
    {
        $this->height = $height;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getThumbnailPath(): ?string
    {
        return $this->thumbnailPath;
    }

    public function setThumbnailPath(?string $thumbnailPath): void
    {
        $this->thumbnailPath = $thumbnailPath;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getAltText(): ?string
    {
        return $this->altText;
    }

    public function setAltText(?string $altText): void
    {
        $this->altText = $altText;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?int $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getFormattedSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
}

