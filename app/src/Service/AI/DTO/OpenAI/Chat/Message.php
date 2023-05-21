<?php
namespace App\Service\AI\DTO\OpenAI\Chat;

final class Message
{

    private string $role;
    private string $content;

    public function __construct(string $role, string $content)
    {
        $this->role = $role;
        $this->content = $content;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
