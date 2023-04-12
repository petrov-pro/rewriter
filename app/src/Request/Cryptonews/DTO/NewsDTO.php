<?php
namespace App\Request\Cryptonews\DTO;

use App\MessageHandler\Message\ContextInterface;
use DateTimeInterface;
use Symfony\Component\Serializer\Annotation\SerializedName;

class NewsDTO implements ContextInterface
{

    private string $news_url;
    private string $image_url;
    private string $title;

    #[SerializedName('text')]
    private string $description;
    private string $text;
    private string $source_name;
    private DateTimeInterface $date;
    private array $topics;
    private string $sentiment;
    private string $type;
    private string $tickers;
    private string $lang = 'en';
    private int $id;

    public function getSourceUrl(): string
    {
        return $this->news_url;
    }

    public function getNewsUrl(): string
    {
        return $this->news_url;
    }

    public function getImageUrl(): string
    {
        return $this->image_url;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getSourceName(): string
    {
        return $this->source_name;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function getTopics(): array
    {
        return $this->topics;
    }

    public function getSentiment(): string
    {
        return $this->sentiment;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTickers(): string
    {
        return $this->tickers;
    }

    public function setNewsUrl(string $news_url): self
    {
        $this->news_url = $news_url;
        return $this;
    }

    public function setImageUrl(string $image_url): self
    {
        $this->image_url = $image_url;
        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function setSourceName(string $source_name): self
    {
        $this->source_name = $source_name;
        return $this;
    }

    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function setTopics(array $topics): self
    {
        $this->topics = $topics;
        return $this;
    }

    public function setSentiment(string $sentiment): self
    {
        $this->sentiment = $sentiment;
        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function setTickers(string $tickers): self
    {
        $this->tickers = $tickers;
        return $this;
    }

    public function getCategory(): array
    {
        return $this->topics;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setLang(string $lang): self
    {
        $this->lang = $lang;
        return $this;
    }
}
