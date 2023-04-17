<?php
namespace App\Entity;

use App\Repository\ContextRepository;
use App\Util\APIEnum;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ContextRepository::class)]
#[ORM\UniqueConstraint(
        columns: ['source_name', 'title']
    )]
class Context
{

    public const STATUS_INIT = 'init';
    public const STATUS_NOT_FOUND = 'not_found';
    public const STATUS_FINISH = 'finish';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $source_url = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $image_url = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text = null;

    #[ORM\Column(length: 2)]
    private ?string $lang = null;

    #[Groups([APIEnum::GROUP_NAME->value])]
    #[ORM\Column(length: 255)]
    private ?string $source_name = null;

    #[Groups([APIEnum::GROUP_NAME->value])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $date = null;

    #[Groups([APIEnum::GROUP_NAME->value])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sentiment = null;

    #[Groups([APIEnum::GROUP_NAME->value])]
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private array $category = [];

    #[Groups([APIEnum::GROUP_NAME->value])]
    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[Groups([APIEnum::GROUP_NAME->value])]
    #[ORM\OneToMany(mappedBy: 'context', targetEntity: Translate::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $translates;

    #[ORM\Column(length: 10)]
    private ?string $status = null;

    #[ORM\OneToMany(mappedBy: 'context', targetEntity: Image::class, orphanRemoval: true)]
    private Collection $images;

    public function __construct()
    {
        $this->translates = new ArrayCollection();
        $this->images = new ArrayCollection();
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

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(string $lang): self
    {
        $this->lang = $lang;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setContext($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getContext() === $this) {
                $image->setContext(null);
            }
        }

        return $this;
    }
}
