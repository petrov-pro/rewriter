<?php
namespace App\Service;

use App\Entity\Account;
use App\Entity\Billing;
use App\Repository\AccountRepository;
use App\Repository\UserRepository;
use Exception;

class AccountService
{

    public const MIN_BALANCE = 500;

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

    public function withdraw(int $amount, int $customerId, bool $flush = false): void
    {
        $account = $this->findAccount($customerId);

        $account->setBalance(
            $account->getBalance() - $amount
        )->addBilling(
            (new Billing())
                ->setSum($amount)
                ->setType(Billing::TYPE_WITHDRAW)
                ->setSystem(Billing::TYPE_WITHDRAW)
                ->setCustomer($this->userRepository->findOrThrow($customerId))
        );

        $this->accountRepository->save($account, $flush);
    }

    public function findAccount(int $customerId): Account
    {
        $account = $this->accountRepository->findOneBy(
            [
                'customer' => $customerId
            ]
        );

        if (!$account) {
            throw new Exception('Not found account for customer: ' . $customerId);
        }

        return $account;
    }
}
