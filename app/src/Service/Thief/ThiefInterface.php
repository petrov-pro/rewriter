<?php
namespace App\Service\Thief;

interface ThiefInterface
{

    public function getData(string $url): string;
}
