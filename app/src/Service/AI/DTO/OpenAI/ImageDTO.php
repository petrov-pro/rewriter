<?php
namespace App\Service\AI\DTO\OpenAI;

use App\Service\AI\DTO\ImageInterface;

final class ImageDTO implements ImageInterface
{

    public const COST = 0.04;

    private int $created;

    /** @var Data[] */
    private array $data;

    /**
     * @param Data[] $data
     */
    public function __construct(int $created, array $data)
    {
        $this->created = $created;
        $this->data = $data;
    }

    public function getCreated(): int
    {
        return $this->created;
    }

    /**
     * @return Data[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function getCost(): int
    {
        return count($this->data);
    }

    public function getImage(): string
    {
        return $this->data[0]->getUrl();
    }

    public function getImages(): array
    {
        $result = [];
        foreach ($this->data as $image) {
            $result[] = $image->getUrl();
        };

        return $result;
    }
}
