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

    public function getLang(): string;

    public function getUserId(): int;

    public function getId(): int;

    public function setId(int $id): self;

    public function setUserId(int $userId): self;

    public function setText(string $text): self;

    public function setLang(string $lang): self;

    public function getSiteId(): int;

    public function setSiteId(int $id): self;

    public function getOriginalLang(): string;

    public function getProvider(): string;

    public function setProvider(string $provider): self;

    public function setTitle(string $title): self;

    public function setDescription(string $description): self;
}
