<?php

namespace App;

enum ProductType: string
{
    case MEDICAL_SUPPLIES = 'medical_supplies';
    case MEDICAL_EQUIPMENT = 'medical_equipment';

    /**
     * Get the display name for the product type.
     */
    public function displayName(): string
    {
        return match ($this) {
            self::MEDICAL_SUPPLIES => 'Medical Supplies',
            self::MEDICAL_EQUIPMENT => 'Medical Equipment',
        };
    }

    /**
     * Get all available product types.
     *
     * @return array<ProductType>
     */
    public static function all(): array
    {
        return [
            self::MEDICAL_SUPPLIES,
            self::MEDICAL_EQUIPMENT,
        ];
    }

    /**
     * Get all product type values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $type) => $type->value, self::all());
    }
}
