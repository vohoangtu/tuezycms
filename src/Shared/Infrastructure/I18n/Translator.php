<?php

declare(strict_types=1);

namespace Shared\Infrastructure\I18n;

class Translator
{
    private static ?self $instance = null;
    private string $locale = 'vi';
    private array $translations = [];

    private function __construct()
    {
        $this->loadTranslations();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
        $this->loadTranslations();
    }

    /**
     * Get current locale
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Load translations for current locale
     */
    private function loadTranslations(): void
    {
        $translationFile = __DIR__ . '/../../../../storage/translations/' . $this->locale . '.php';
        
        if (file_exists($translationFile)) {
            $this->translations = require $translationFile;
        } else {
            // Load default translations
            $this->translations = $this->getDefaultTranslations();
        }
    }

    /**
     * Translate a key
     */
    public function trans(string $key, array $replace = []): string
    {
        $keys = explode('.', $key);
        $value = $this->translations;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $key; // Return key if translation not found
            }
            $value = $value[$k];
        }

        $translation = is_string($value) ? $value : $key;

        // Replace placeholders
        foreach ($replace as $placeholder => $replacement) {
            $translation = str_replace(':' . $placeholder, (string)$replacement, $translation);
        }

        return $translation;
    }

    /**
     * Get default translations
     */
    private function getDefaultTranslations(): array
    {
        return [
            'vi' => [
                'home' => 'Trang chủ',
                'contact' => 'Liên hệ',
                'news' => 'Tin tức',
                'services' => 'Dịch vụ',
                'knowledge' => 'Kiến thức',
                'products' => 'Sản phẩm',
                'cart' => 'Giỏ hàng',
                'read_more' => 'Đọc thêm',
                'add_to_cart' => 'Thêm vào giỏ hàng',
                'price' => 'Giá',
                'old_price' => 'Giá cũ',
                'new_price' => 'Giá mới',
                'promotional_price' => 'Giá khuyến mãi',
                'stock' => 'Tồn kho',
                'views' => 'Lượt xem',
                'not_found' => 'Không tìm thấy',
            ],
            'en' => [
                'home' => 'Home',
                'contact' => 'Contact',
                'news' => 'News',
                'services' => 'Services',
                'knowledge' => 'Knowledge',
                'products' => 'Products',
                'cart' => 'Cart',
                'read_more' => 'Read more',
                'add_to_cart' => 'Add to cart',
                'price' => 'Price',
                'old_price' => 'Old price',
                'new_price' => 'New price',
                'promotional_price' => 'Promotional price',
                'stock' => 'Stock',
                'views' => 'Views',
                'not_found' => 'Not found',
            ],
        ][$this->locale] ?? [];
    }

    /**
     * Check if translation exists
     */
    public function has(string $key): bool
    {
        $keys = explode('.', $key);
        $value = $this->translations;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return false;
            }
            $value = $value[$k];
        }

        return is_string($value);
    }
}

