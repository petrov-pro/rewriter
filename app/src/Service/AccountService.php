<?php
namespace App\Service;

use App\Entity\Account;
use App\Entity\Billing;
use App\Repository\AccountRepository;
use App\Repository\UserRepository;
use DateTime;
use InvalidArgumentException;

class AccountService
{

    public const MIN_BALANCE = 1000000;
    //one dollar cost 1000000
    public const DIMENSION_TOKEN = 1000000;

    public function __construct(
        private AccountRepository $accountRepository,
        private UserRepository $userRepository
    )
    {
        
    }

    public function isEnoughBalance(int $customerId, int $price): bool
    {
        $account = $this->findAccount($customerId);

        return (($account->getBalance() - $price) >= self::MIN_BALANCE);
    }

    public function withdraw(int $amount, int $customerId, bool $flush = false): Account
    {
        $account = $this->findAccount($customerId);

        $account->setBalance(
            $account->getBalance() - $amount
        )->addBilling(
            (new Billing())
                ->setSum($amount)
                ->setType(Billing::TYPE_WITHDRAW)
                ->setSystem(Billing::TYPE_WITHDRAW)
                ->setDate(new DateTime('now'))
                ->setCustomer($this->userRepository->findOrThrow($customerId))
        );

        $this->accountRepository->save($account, $flush);

        return $account;
    }

    public function findAccount(int $customerId): Account
    {
        $account = $this->accountRepository->findOneBy(
            [
                'customer' => $customerId
            ]
        );

        if (!$account) {
            throw new InvalidArgumentException('Not found account for customer: ' . $customerId);
        }

        return $account;
    }

    public function setBalance(int $amount, int $customerId, bool $flush = false): Account
    {
        try {
            $account = $this->findAccount($customerId);
        } catch (InvalidArgumentException $ex) {
            $account = (new Account())
                ->setCustomer($this->userRepository->findOrThrow($customerId));
        }

        $account->setBalance($amount)
            ->addBilling((new Billing())
                ->setCustomer($account->getCustomer())
                ->setType(Billing::TYPE_MODIFY)
                ->setSystem(Billing::SYSTEM)
                ->setDate(new DateTime('now'))
                ->setSum($amount)
        );

        $this->accountRepository->save($account, $flush);

        return $account;
    }

    public function deposit(int $amount, int $customerId, bool $flush = false): Account
    {
        $amount = $amount * self::DIMENSION_TOKEN;
        $account = $this->findAccount($customerId);
        $account->addBalance($amount)
            ->addBilling((new Billing())
                ->setCustomer($account->getCustomer())
                ->setType(Billing::TYPE_DEPOSIT)
                ->setSystem(Billing::SYSTEM)
                ->setDate(new DateTime('now'))
                ->setSum($amount)
        );

        $this->accountRepository->save($account, $flush);

        return $account;
    }
}
