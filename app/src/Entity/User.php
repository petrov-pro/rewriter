<?php
namespace App\Entity;

use App\Repository\UserRepository;
use App\Util\APIEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups([APIEnum::GROUP_NAME->value])]
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[Groups([APIEnum::GROUP_NAME->value])]
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[Groups([APIEnum::GROUP_NAME->value])]
    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    private array $lang = [];

    #[Groups([APIEnum::GROUP_NAME->value])]
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private array $context_category = [];

    #[Groups([APIEnum::GROUP_NAME->value])]
    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: APIToken::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $apiTokens;

    #[Groups([APIEnum::GROUP_NAME->value])]
    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Billing::class)]
    private Collection $billings;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Image::class)]
    private Collection $images;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Translate::class)]
    private Collection $translates;

    #[Groups([APIEnum::GROUP_NAME->value])]
    #[ORM\OneToOne(mappedBy: 'customer', cascade: ['persist', 'remove'])]
    private ?Account $account = null;

    public function __construct()
    {
        $this->apiTokens = new ArrayCollection();
        $this->billings = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->translates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getContextCategory(): array
    {
        return $this->context_category;
    }

    public function setContextCategory(?array $context_category): self
    {
        $this->context_category = $context_category;

        return $this;
    }

    /**
     * @return Collection<int, APIToken>
     */
    public function getAPITokens(): Collection
    {
        return $this->apiTokens;
    }

    public function addAPIToken(APIToken $aPIToken): self
    {
        if (!$this->apiTokens->contains($aPIToken)) {
            $this->apiTokens->add($aPIToken);
            $aPIToken->setCustomer($this);
        }

        return $this;
    }

    public function removeAPIToken(APIToken $aPIToken): self
    {
        if ($this->apiTokens->removeElement($aPIToken)) {
            // set the owning side to null (unless already changed)
            if ($aPIToken->getCustomer() === $this) {
                $aPIToken->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Billing>
     */
    public function getBillings(): Collection
    {
        return $this->billings;
    }

    public function addBilling(Billing $billing): self
    {
        if (!$this->billings->contains($billing)) {
            $this->billings->add($billing);
            $billing->setCustomer($this);
        }

        return $this;
    }

    public function removeBilling(Billing $billing): self
    {
        if ($this->billings->removeElement($billing)) {
            // set the owning side to null (unless already changed)
            if ($billing->getCustomer() === $this) {
                $billing->setCustomer(null);
            }
        }

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
            $image->setCustomer($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getCustomer() === $this) {
                $image->setCustomer(null);
            }
        }

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
            $translate->setCustomer($this);
        }

        return $this;
    }

    public function removeTranslate(Translate $translate): self
    {
        if ($this->translates->removeElement($translate)) {
            // set the owning side to null (unless already changed)
            if ($translate->getCustomer() === $this) {
                $translate->setCustomer(null);
            }
        }

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): self
    {
        // set the owning side of the relation if necessary
        if ($account->getCustomer() !== $this) {
            $account->setCustomer($this);
        }

        $this->account = $account;

        return $this;
    }
}
