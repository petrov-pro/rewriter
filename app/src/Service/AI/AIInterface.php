<?php
namespace App\Service\AI;

interface AIInterface
{

    public function rewrite(string $textRewrite, string $translateTo = ''): string;

    public function keywords(string $title, int $count = 3): string;

    public function createImage(string $prompt, string $type = 'url'): string;

    public function findCountToken(string $text): int;
}
