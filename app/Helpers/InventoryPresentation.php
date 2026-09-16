<?php

namespace App\Helpers;

final class InventoryPresentation
{
    public static function health(array $item): array
    {
        if ((int) $item['stok'] === 0) {
            return ['key' => 'out', 'label' => 'Habis', 'icon' => '×'];
        }
        if ((int) $item['stok'] <= (int) $item['stok_minimum']) {
            return ['key' => 'low', 'label' => 'Stok Rendah', 'icon' => '!'];
        }
        return ['key' => 'safe', 'label' => 'Aman', 'icon' => '✓'];
    }

    public static function placeholder(string $category): string
    {
        $file = match ($category) {
            'Laptop & Komputer' => 'laptop',
            'Monitor & Display' => 'monitor',
            'Komponen Komputer' => 'components',
            'Peripheral' => 'peripheral',
            'Networking' => 'network',
            'IoT & Embedded' => 'embedded',
            default => 'generic',
        };
        return '/assets/images/categories/' . $file . '.svg';
    }

    public static function photo(array $item): string
    {
        return !empty($item['foto']) ? '/media/barang/' . (int) $item['id'] : self::placeholder($item['kategori']);
    }
}
