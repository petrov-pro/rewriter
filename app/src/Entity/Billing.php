<?php
namespace App\Entity;

use App\Repository\BillingRepository;
use App\Util\APIEnum;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Component\Serializer\Annotation\Groups;

#[UniqueConstraint(columns: ['transaction_id'])]
#[ORM\Entity(repositoryClass: BillingRepository::class)]
class Billing
{

    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_MODIFY = 'modify';
    public const TYPE_WITHDRAW = 'withdraw';
    public const SYSTEM = 'system';
    public const SYSTEM_IMAGE = 'image';
    public const SYSTEM_KEYWORD = 'keyword';
    public const SYSTEM_REWRITE = 'rewrite';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups([APIEnum::GROUP_NAME_SHOW->value])]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $sum = null;

    #[ORM\ManyToOne(inversedBy: 'billings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $customer = null;

    #[ORM\ManyToOne(inversedBy: 'billings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Account $account = null;

    #[Groups([APIEnum::GROUP_NAME_SHOW->value])]
    #[ORM\Column(length: 15)]
    private ?string $type = null;

    #[Groups([APIEnum::GROUP_NAME_SHOW->value])]
    #[ORM\Column(length: 20)]
    private ?string $system = null;

    #[Groups([APIEnum::GROUP_NAME_SHOW->value])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $date = null;

    #[Groups([APIEnum::GROUP_NAME_SHOW->value])]
    #[ORM\Column(length: 255)]
    private ?string $transaction_id = null;

    #[ORM\Column(nullable: true)]
    private ?int $entity_id = null;

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

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getTransactionId(): ?string
    {
        return $this->transaction_id;
    }

    public function setTransactionId(string $transaction_id): self
    {
        $this->transaction_id = $transaction_id;

        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entity_id;
    }

    public function setEntityId(?int $entity_id): self
    {
        $this->entity_id = $entity_id;

        return $this;
    }
}
