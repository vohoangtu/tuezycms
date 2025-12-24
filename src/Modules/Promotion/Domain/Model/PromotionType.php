<?php

declare(strict_types=1);

namespace Modules\Promotion\Domain\Model;

enum PromotionType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';
    case EVENT = 'event';

    public function getValue(): string
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::PERCENTAGE => 'Theo phần trăm',
            self::FIXED => 'Theo đơn giá',
            self::EVENT => 'Theo sự kiện',
        };
    }
}

