<?php
namespace App\Entity;

use App\Repository\TranslateRepository;
use App\Util\APIEnum;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TranslateRepository::class)]
#[ORM\UniqueConstraint(
        columns: ['lang', 'context_id', 'customer_id', 'site_id']
    )]
#[ORM\Index(name: "indx_context_id_site_id_create_at", columns: ['context_id', 'site_id', 'create_at'])]
class Translate
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups([APIEnum::GROUP_NAME_SHOW->value])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $title = null;

    #[Groups([APIEnum::GROUP_NAME_SHOW->value])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text = null;

    #[Groups([APIEnum::GROUP_NAME_SHOW->value])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[Groups([APIEnum::GROUP_NAME_SHOW->value])]
    #[ORM\Column(length: 2)]
    private ?string $lang = null;

    #[Groups([APIEnum::GROUP_NAME_SHOW->value])]
    #[ORM\Column()]
    private ?DateTimeImmutable $create_at = null;

    #[ORM\ManyToOne(inversedBy: 'translates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Context $context = null;

    #[ORM\Column(nullable: true)]
    private ?int $token = null;

    #[ORM\ManyToOne(inversedBy: 'translates')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $customer = null;

    #[Groups([APIEnum::GROUP_NAME_SHOW->value])]
    #[ORM\ManyToOne(inversedBy: 'translate')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Site $site = null;

    public function __construct()
    {
        $this->create_at = new DateTimeImmutable();
    }

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

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }

    public function getCreateAt(): ?DateTimeImmutable
    {
        return $this->create_at;
    }

    public function setCreateAt(DateTimeImmutable $create_at): self
    {
        $this->create_at = $create_at;

        return $this;
    }
}
