<?php
namespace App\Service\AI\DTO;

interface TextInterface
{

    public function getText(): string;

    public function getToken(): int;
}
