<?php
namespace App\Service\Parser;

interface SiteParserInterface
{

    public function parser(string $url): string;

    public function isSupport(string $sourceName): bool;
}
