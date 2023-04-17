<?php
namespace App\Service\AI\DTO\OpenAI;

use App\Service\AI\DTO\TextInterface;

final class TextDTO implements TextInterface
{

    public const COST = 0.12; //or 0.06

    private string $id;
    private string $object;
    private int $created;
    private string $model;

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
        string $model,
        array $choices,
        Usage $usage
    )
    {
        $this->id = $id;
        $this->object = $object;
        $this->created = $created;
        $this->model = $model;
        $this->choices = $choices;
        $this->usage = $usage;
    }

    public function getCost(): int
    {
        return $this->usage->getTotalTokens();
    }

    public function getText(): string
    {
        return $this->choices[0]->getText();
    }
}
