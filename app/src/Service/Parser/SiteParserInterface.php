<?php
namespace App\Service\Parser;

interface SiteParserInterface
{

    public function parser(string $data): string;

    public function isSupport(string $sourceName): bool;
}
