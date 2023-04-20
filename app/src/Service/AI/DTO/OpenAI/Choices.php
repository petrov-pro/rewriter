<?php
namespace App\Service\AI\DTO\OpenAI;

use Symfony\Component\Serializer\Annotation\SerializedName;

final class Choices
{

    private string $text;
    private int $index;
    private null $logprobs;
    #[SerializedName('finish_reason')]
    private string $finishReason;

    public function __construct(
        string $text,
        int $index,
        null $logprobs,
        string $finishReason
    )
    {
        $this->text = $text;
        $this->index = $index;
        $this->logprobs = $logprobs;
        $this->finishReason = $finishReason;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function getLogprobs(): null
    {
        return $this->logprobs;
    }

    public function getFinishReason(): string
    {
        return $this->finishReason;
    }
}
