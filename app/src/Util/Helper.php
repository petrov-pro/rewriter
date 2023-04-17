<?php
namespace App\Util;

final class Helper
{

    public static function generateHash(string $idt, array $params): string
    {
        return str_replace(['/', '\\', '.', '.', ':'], '_', $idt) . md5(serialize($params));
    }

    public static function generateAPITokenHash(string $email): string
    {
        return md5($email . time() . time());
    }
}
