<?php
namespace App\Service\Parser;

interface StructureParserInterface
{

    public function handleResult(mixed $nodes, array $skipWords = [], array $allowTagWithAttribute = []): string;

    public function parse(string $page, string $pattern, array $allowTag = []): mixed;

    public function proccess(string $page, string $pattern, array $allowTag = [], array $skipWords = [], array $allowTagWithAttribute = []): string;
}
