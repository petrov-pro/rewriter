<?php
namespace App\Service\AI\DTO\OpenAI\Chat;

use App\Service\AI\DTO\OpenAI\Usage;
use App\Service\AI\DTO\TextInterface;

final class ChatDTO implements TextInterface
{

    private string $id;
    private string $object;
    private int $created;

    /** @var Choices[] */
    private array $choices;
    private Usage $usage;

    /**
     * @param Choices[] $choices
     */
    public function __construct(
        string $id,
        string $object,
        int $created,
        array $choices,
        Usage $usage
    )
    {
        $this->id = $id;
        $this->object = $object;
        $this->created = $created;
        $this->choices = $choices;
        $this->usage = $usage;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getObject(): string
    {
        return $this->object;
    }

    public function getCreated(): int
    {
        return $this->created;
    }

    /**
     * @return Choices[]
     */
    public function getChoices(): array
    {
        return $this->choices;
    }

    public function getUsage(): Usage
    {
        return $this->usage;
    }

    public function getText(): string
    {
        return $this->choices[0]->getMessage()->getContent();
    }

    public function getToken(): int
    {
        return $this->usage->getTotalTokens();
    }
}
