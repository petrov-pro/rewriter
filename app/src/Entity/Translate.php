<?php
namespace App\Entity;

use App\Repository\TranslateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TranslateRepository::class)]
class Translate
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['show_content'])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $title = null;

    #[Groups(['show_content'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text = null;

    #[Groups(['show_content'])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[Groups(['show_content'])]
    #[ORM\Column(length: 2)]
    private ?string $lang = null;

    #[ORM\Column(length: 10)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'translates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Context $context = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getContext(): ?Context
    {
        return $this->context;
    }

    public function setContext(?Context $context): self
    {
        $this->context = $context;

        return $this;
    }
}
