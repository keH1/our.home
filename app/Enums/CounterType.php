<?php

namespace App\Enums;

enum CounterType: string
{
    case ELECTRICITY = 'Электроэнергия';
    case COLD_WATER = 'Холодное водоснабжение';
    case COLD_WATER_TWO_POINTS = 'Холодное водоснабжение (2 точки доступа)';
    case WARM_WATER = 'Горячее водоснабжение';
    case WARM_WATER_TWO_POINTS = "Горячее водоснабжение(2 точки доступа)";
    case GAS = 'Газ';
    case HEATING = 'Отопление';

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
                'Горячее водоснабжение',
            ],
            CounterType::COLD_WATER_TWO_POINTS => [
                'Холодное водоснабжение (2 точки доступа)',
            ],
            CounterType::WARM_WATER_TWO_POINTS => [
                'Горячее водоснабжение(2 точки доступа)'
            ],
            CounterType::GAS => [
                'Газ'
            ],
            CounterType::HEATING => [
                'Отопление'
            ],
        };
    }
}
