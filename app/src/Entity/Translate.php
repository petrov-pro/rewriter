<?php
namespace App\Entity;

use App\Repository\TranslateRepository;
use App\Util\APIEnum;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TranslateRepository::class)]
#[ORM\UniqueConstraint(
        columns: ['lang', 'context_id', 'customer_id']
    )]
class Translate
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups([APIEnum::GROUP_NAME->value])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $title = null;

    #[Groups([APIEnum::GROUP_NAME->value])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text = null;

    #[Groups([APIEnum::GROUP_NAME->value])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[Groups([APIEnum::GROUP_NAME->value])]
    #[ORM\Column(length: 2)]
    private ?string $lang = null;

    #[ORM\ManyToOne(inversedBy: 'translates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Context $context = null;

    #[ORM\Column(nullable: true)]
    private ?int $token = null;

    #[ORM\ManyToOne(inversedBy: 'translates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $customer = null;

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

    public function getContext(): ?Context
    {
        return $this->context;
    }

    public function setContext(?Context $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function getToken(): ?int
    {
        return $this->token;
    }

    public function setToken(?int $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getCustomer(): ?User
    {
        return $this->customer;
    }

    public function setCustomer(?User $customer): self
    {
        $this->customer = $customer;

        return $this;
    }
}
