<?php
namespace App\Util;

use Nette\Utils\Strings;

class NormalizeText
{

    public static function handle(string $text): string
    {
        return Strings::normalize(Strings::toAscii(Strings::fixEncoding($text)));
    }
}
