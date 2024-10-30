<?php

namespace App\Enums;

enum ClaimType: string
{
    case BASE = 'base';
    case PAID = 'paid';
    case SEASON = 'season';

    /**
     * Получить специфичные правила валидации для типа заявки.
     *
     * @param  bool  $required
     * @return array
     */
    public function getValidationRules(bool $required = true): array
    {
        $requirement = $required ? 'required' : 'sometimes';

        return match ($this) {
            self::BASE => [
                'category_id' => "$requirement|integer|exists:claim_categories,id",
            ],
            self::PAID => [
                'service_id' => "$requirement|integer|exists:paid_services,id",
                'is_paid' => "$requirement|boolean",
            ],
            self::SEASON => [
                'expectation_date' => "$requirement|date",
            ],
        };
    }

    /**
     * Получить специфичные поля данных для типа заявки.
     *
     * @return array
     */
    public function getDataFieldMappings(): array
    {
        return match ($this) {
            self::BASE => [
                'category_id',
            ],
            self::PAID => [
                'paid_service_id' => 'service_id',
                'is_paid',
            ],
            self::SEASON => [
                'expectation_date',
            ],
        };
    }
}
