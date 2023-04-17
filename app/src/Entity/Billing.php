<?php
namespace App\Entity;

use App\Repository\BillingRepository;
use App\Util\APIEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BillingRepository::class)]
class Billing
{

    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAW = 'withdraw';
    public const SYSTEM = 'system';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups([APIEnum::GROUP_NAME->value])]
    #[ORM\Column]
    private ?int $sum = null;

    #[ORM\ManyToOne(inversedBy: 'billings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $customer = null;

    #[ORM\ManyToOne(inversedBy: 'billings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Account $account = null;

    #[Groups([APIEnum::GROUP_NAME->value])]
    #[ORM\Column(length: 15)]
    private ?string $type = null;

    #[Groups([APIEnum::GROUP_NAME->value])]
    #[ORM\Column(length: 20)]
    private ?string $system = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSum(): ?int
    {
        return $this->sum;
    }

    public function setSum(int $sum): self
    {
        $this->sum = $sum;

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

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): self
    {
        $this->account = $account;

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

    public function getSystem(): ?string
    {
        return $this->system;
    }

    public function setSystem(string $system): self
    {
        $this->system = $system;

        return $this;
    }
}
