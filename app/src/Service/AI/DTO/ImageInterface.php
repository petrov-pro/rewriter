<?php
namespace App\Service\AI\DTO;

interface ImageInterface
{

    public function getImages(): array;

    public function getCost(): int;
}
