<?php
namespace App\Entity;

use App\Repository\ContextRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContextRepository::class)]
#[ORM\UniqueConstraint(
        columns: ['source_name', 'hash']
    )]
class Context
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['show_content'])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $source_url = null;

    #[Groups(['show_content'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $image_url = null;

    #[Groups(['show_content'])]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[Groups(['show_content'])]
    #[ORM\Column(length: 255)]
    private ?string $source_name = null;

    #[Groups(['show_content'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $date = null;

    #[Groups(['show_content'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sentiment = null;

    #[Groups(['show_content'])]
    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $category = [];

    #[Groups(['show_content'])]
    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $hash = null;

    #[Groups(['show_content'])]
    #[ORM\OneToMany(mappedBy: 'context', targetEntity: Translate::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $translates;

    public function __construct()
    {
        $this->translates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSourceUrl(): ?string
    {
        return $this->source_url;
    }

    public function setSourceUrl(string $source_url): self
    {
        $this->source_url = $source_url;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }

    public function setImageUrl(string $image_url): self
    {
        $this->image_url = $image_url;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSourceName(): ?string
    {
        return $this->source_name;
    }

    public function setSourceName(string $source_name): self
    {
        $this->source_name = $source_name;

        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getSentiment(): ?string
    {
        return $this->sentiment;
    }

    public function setSentiment(?string $sentiment): self
    {
        $this->sentiment = $sentiment;

        return $this;
    }

    public function getCategory(): array
    {
        return $this->category;
    }

    public function setCategory(?array $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(?string $hash): self
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @return Collection<int, Translate>
     */
    public function getTranslates(): Collection
    {
        return $this->translates;
    }

    public function addTranslate(Translate $translate): self
    {
        if (!$this->translates->contains($translate)) {
            $this->translates->add($translate);
            $translate->setContext($this);
        }

        return $this;
    }

    public function removeTranslate(Translate $translate): self
    {
        if ($this->translates->removeElement($translate)) {
            // set the owning side to null (unless already changed)
            if ($translate->getContext() === $this) {
                $translate->setContext(null);
            }
        }

        return $this;
    }
}
