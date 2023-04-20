<?php
namespace App\Service\AI\DTO\OpenAI;

final class Data
{

    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
    
    
}
