<?php
namespace App\Util;

enum FetchContentPeriodTypeEnum: string
{
    case ALWAYS = 'always';
    case EVERY_10_MINUTE = 'PT10I';
    case EVERY_30_MINUTE = 'PT30I';
    case EVERY_1_HOUR = 'PT1H';
    case EVERY_2_HOUR = 'PT2H';
    case EVERY_3_HOUR = 'PT3H';
    case EVERY_4_HOUR = 'PT4H';
    case EVERY_5_HOUR = 'PT5H';
    case EVERY_6_HOUR = 'PT6H';
    case EVERY_7_HOUR = 'PT7H';
    case EVERY_8_HOUR = 'PT8H';
    case EVERY_9_HOUR = 'PT9H';
    case EVERY_10_HOUR = 'PT10H';
    case EVERY_11_HOUR = 'PT11H';
    case EVERY_12_HOUR = 'PT12H';
    case EVERY_1_DAY = 'P1D';

    public static function getArray(): array
    {
        return array_column(AITypeEnum::cases(), 'value');
    }
}
