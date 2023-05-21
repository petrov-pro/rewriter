<?php
namespace App\Service\AI\DTO\OpenAI\Chat;

use Symfony\Component\Serializer\Annotation\SerializedName;

final class Choices
{

    private int $index;
    private Message $message;
    #[SerializedName('finish_reason')]
    private string $finishReason;

    public function __construct(
        int $index,
        Message $message,
        string $finishReason
    )
    {
        $this->index = $index;
        $this->message = $message;
        $this->finishReason = $finishReason;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }

    public function getFinishReason(): string
    {
        return $this->finishReason;
    }
}
