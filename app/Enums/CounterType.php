<?php

namespace App\Enums;

enum CounterType: string
{
    case ELECTRICITY = 'Электроэнергия';
    case COLD_WATER = 'Холодное водоснабжение';
    case WARM_WATER = 'Горячее водоснабжение';
    case GAS = 'Газ';

    /**
     * @return string[]
     */
    public function counterTypes(): array
    {
        return match ($this) {
            CounterType::ELECTRICITY => [
                'Электроэнергия',
            ],
            CounterType::COLD_WATER => [
                'Холодное водоснабжение',
                'Холодное водоснабжение ',
            ],
            CounterType::WARM_WATER => [
                'Горячее водоснабжение'
            ],
            CounterType::GAS => [
                'Газ'
            ],
        };
    }
}
