<?php
namespace App\Service\AI\DTO\OpenAI;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Usage
{

    #[SerializedName('prompt_tokens')]
    private int $promptTokens;

    #[SerializedName('completion_tokens')]
    private int $completionTokens;

    #[SerializedName('total_tokens')]
    private int $totalTokens;

    public function __construct(
        int $promptTokens,
        int $completionTokens,
        int $totalTokens
    )
    {
        $this->promptTokens = $promptTokens;
        $this->completionTokens = $completionTokens;
        $this->totalTokens = $totalTokens;
    }

    public function getPromptTokens(): int
    {
        return $this->promptTokens;
    }

    public function getCompletionTokens(): int
    {
        return $this->completionTokens;
    }

    public function getTotalTokens(): int
    {
        return $this->totalTokens;
    }
}
