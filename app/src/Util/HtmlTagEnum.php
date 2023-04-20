<?php
namespace App\Util;

enum HtmlTagEnum: string
{

    case TAG_DEFAULT = 'tag_default';
    case TAG_NOT_USE = 'tag_not_use';
    case TAG_AI = 'tag_ai';
    case TAG_USER = 'tag_user';

    public static function getArray(): array
    {
        return array_column(HtmlTagEnum::cases(), 'value');
    }
}
