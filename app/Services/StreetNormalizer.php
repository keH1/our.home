<?php

namespace App\Services;

class StreetNormalizer
{
    /**
     * @var array|array[]
     */
    public static array $customStreetsPatterns = [
        [
            'needablePattern' => 'Сакко и Ванцетти',
            'replacements' => [
                'и Ванцетти Сакко'
            ]
        ]
    ];


    /**
     * @param string $streetName
     * @return string
     */
    public static function normalizeStreetName(string $streetName): string
    {
        $streetName = trim($streetName);
        $streetName = str_replace('ул.', 'ул', $streetName);
        $streetName = str_replace('пер.', 'пер', $streetName);

        foreach (self::$customStreetsPatterns as $streetsPattern) {
            if (in_array($streetName,$streetsPattern['replacements'])) {
                $streetName = $streetsPattern['needablePattern'];
            }
        }

        if (!str_ends_with($streetName,' ул')){
            $streetName .= ' ул';
        }

        return $streetName;
    }
}
