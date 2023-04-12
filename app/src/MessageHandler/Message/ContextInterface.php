<?php
namespace App\MessageHandler\Message;

use DateTimeInterface;

interface ContextInterface
{

    public function getSourceName(): string;

    public function getTitle(): string;

    public function getSourceUrl(): string;

    public function getDescription(): string;

    public function getSentiment(): string;

    public function getImageUrl(): string;

    public function getType(): string;

    public function getCategory(): array;

    public function getDate(): DateTimeInterface;

    public function getText(): string;

    public function setText(string $text): self;

    public function getLang(): string;

    public function getId(): int;

    public function setId(int $id): self;
}
