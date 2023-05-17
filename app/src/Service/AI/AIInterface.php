<?php
namespace App\Service\AI;

use App\Service\AI\DTO\ImageInterface;
use App\Service\AI\DTO\TextInterface;
use App\Util\TypeDataEnum;

interface AIInterface
{

    public function rewrite(mixed $idt, string $textRewrite, string $langOriginal, string $translateTo = '', string $type = ''): TextInterface;

    public function keywords(mixed $idt, string $title, int $count = 6): TextInterface;

    public function createImage(mixed $idt, string $prompt, string $type = 'url'): ImageInterface;

    public function findSupposedCost(TypeDataEnum $type, mixed $token): int;

    public function findCost(TypeDataEnum $type, int $token): int;
}
