<?php
namespace App\Entity;

use App\Repository\SiteRepository;
use App\Service\Spread\WordPress\WordPressProvider;
use App\Util\APIEnum;
use App\Util\CategoryMainEnum;
use App\Util\AITypeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SiteRepository::class)]
class Site
{

    public const TYPE = [WordPressProvider::TYPE, 'none'];
    public const HTML_TAGS = [
        AITypeEnum::TAG_AI->value,
        AITypeEnum::TAG_DEFAULT->value,
        AITypeEnum::TAG_NOT_USE->value,
        AITypeEnum::TAG_USER->value,
    ];
    public const CATEGORIES = [
        CategoryMainEnum::CRYPTO->value
    ];

    #[Groups([APIEnum::GROUP_NAME_SHOW->value])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank()]
    #[Assert\Url]
    #[Groups([APIEnum::GROUP_NAME_SHOW->value, APIEnum::GROUP_NAME_CREATE->value, APIEnum::GROUP_NAME_UPDATE->value])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $url = null;

    #[Assert\NotBlank()]
    #[Assert\Type('bool')]
    #[Groups([APIEnum::GROUP_NAME_SHOW->value, APIEnum::GROUP_NAME_CREATE->value, APIEnum::GROUP_NAME_UPDATE->value])]
    #[ORM\Column]
    private ?bool $is_valid = true;

    /**
     * @var string[]
     */
    #[Groups([APIEnum::GROUP_NAME_SHOW->value, APIEnum::GROUP_NAME_CREATE->value, APIEnum::GROUP_NAME_UPDATE->value])]
    #[ORM\Column(nullable: true)]
    private array $setting = [];

    #[Assert\Choice(choices: Site::TYPE)]
    #[Groups([APIEnum::GROUP_NAME_SHOW->value, APIEnum::GROUP_NAME_CREATE->value, APIEnum::GROUP_NAME_UPDATE->value])]
    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[Assert\Choice(choices: Site::HTML_TAGS)]
    #[Groups([APIEnum::GROUP_NAME_SHOW->value, APIEnum::GROUP_NAME_CREATE->value, APIEnum::GROUP_NAME_UPDATE->value])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $html_tag = AITypeEnum::TAG_AI->value;

    #[Assert\Type('bool')]
    #[Groups([APIEnum::GROUP_NAME_SHOW->value, APIEnum::GROUP_NAME_CREATE->value, APIEnum::GROUP_NAME_UPDATE->value])]
    #[ORM\Column]
    private ?bool $is_image = false;

    #[Assert\NotBlank()]
    #[Groups([APIEnum::GROUP_NAME_SHOW->value, APIEnum::GROUP_NAME_CREATE->value, APIEnum::GROUP_NAME_UPDATE->value])]
    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    private array $category = [];

    #[Assert\NotBlank()]
    #[Assert\Lang()]
    #[Groups([APIEnum::GROUP_NAME_SHOW->value, APIEnum::GROUP_NAME_CREATE->value, APIEnum::GROUP_NAME_UPDATE->value])]
    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    private array $lang = [];

    #[Assert\Type('bool')]
    #[Groups([APIEnum::GROUP_NAME_SHOW->value, APIEnum::GROUP_NAME_CREATE->value, APIEnum::GROUP_NAME_UPDATE->value])]
    #[ORM\Column]
    private ?bool $is_send = false;

    #[ORM\ManyToOne(inversedBy: 'sites')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $customer = null;

    #[ORM\OneToMany(mappedBy: 'site', targetEntity: Translate::class)]
    private Collection $translate;

    #[ORM\OneToMany(mappedBy: 'site', targetEntity: Image::class)]
    private Collection $image;

    public function __construct()
    {
        $this->translate = new ArrayCollection();
        $this->image = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function isValid(): ?bool
    {
        return $this->is_valid;
    }

    public function setIsValid(bool $is_valid): self
    {
        $this->is_valid = $is_valid;

        return $this;
    }

    public function getSetting(): array
    {
        return $this->setting;
    }

    public function setSetting(?array $setting): self
    {
        $this->setting = $setting;

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

    public function getHtmlTag(): ?string
    {
        return $this->html_tag;
    }

    public function setHtmlTag(?string $html_tag): self
    {
        $this->html_tag = $html_tag;

        return $this;
    }

    public function isImage(): ?bool
    {
        return $this->is_image;
    }

    public function setIsImage(bool $is_image): self
    {
        $this->is_image = $is_image;

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

    /**
     * @return Collection<int, Translate>
     */
    public function getTranslate(): Collection
    {
        return $this->translate;
    }

    public function addTranslate(Translate $translate): self
    {
        if (!$this->translate->contains($translate)) {
            $this->translate->add($translate);
            $translate->setSite($this);
        }

        return $this;
    }

    public function removeTranslate(Translate $translate): self
    {
        if ($this->translate->removeElement($translate)) {
            // set the owning side to null (unless already changed)
            if ($translate->getSite() === $this) {
                $translate->setSite(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImage(): Collection
    {
        return $this->image;
    }

    public function addImage(Image $image): self
    {
        if (!$this->image->contains($image)) {
            $this->image->add($image);
            $image->setSite($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->image->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getSite() === $this) {
                $image->setSite(null);
            }
        }

        return $this;
    }

    public function getCategory(): array
    {
        return $this->category;
    }

    public function setCategory(array $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function isSend(): ?bool
    {
        return $this->is_send;
    }

    public function setIsSend(bool $is_send): self
    {
        $this->is_send = $is_send;

        return $this;
    }

    public function getLang(): array
    {
        return $this->lang;
    }

    public function setLang(array $lang): self
    {
        $this->lang = $lang;

        return $this;
    }
}
