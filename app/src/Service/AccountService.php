<?php
namespace App\Service;

use App\Entity\Account;
use App\Entity\Billing;
use App\Exception\NotFoundException;
use App\Repository\AccountRepository;
use App\Repository\BillingRepository;
use App\Repository\UserRepository;
use DateTime;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class AccountService
{

    public const MIN_BALANCE = 1000000;
    //one dollar cost 1000000
    public const DIMENSION_TOKEN = 1000000;

    public function __construct(
        private AccountRepository $accountRepository,
        private UserRepository $userRepository,
        private BillingRepository $billingRepository,
        private LoggerInterface $logger
    )
    {
        
    }

    public function isEnoughBalance(int $customerId, int $price): bool
    {
        $account = $this->findAccount($customerId);

        return (($account->getBalance() - $price) >= self::MIN_BALANCE);
    }

    public function withdraw(int $amount, int $customerId, bool $flush = false, string $typeEntity = Billing::SYSTEM, int $entityId = null, string $transactionId = ''): Account
    {
        if (!$transactionId) {
            $transactionId = $this->generateTransactionId($customerId);
        } else {
            $transaction = $this->billingRepository->findOneBy(['transaction_id' => $transactionId]);
            if ($transaction) {
                return $this->findAccount($customerId);
            }
        }

        try {
            $this->accountRepository->startTransaction();
            $account = $this->accountRepository->findLockBy($customerId);

            $currentAccount = $account->getBalance();
            $account->setBalance(
                $currentAccount - $amount
            )->addBilling(
                (new Billing())
                    ->setSum($amount)
                    ->setType(Billing::TYPE_WITHDRAW)
                    ->setTransactionId($transactionId)
                    ->setSystem($typeEntity)
                    ->setEntityId($entityId)
                    ->setDate(new DateTime('now'))
                    ->setCustomer($this->userRepository->findOrThrow($customerId))
            );

            $this->accountRepository->save($account, $flush);
            if ($flush) {
                $this->accountRepository->commitTransaction();
            }
        } catch (\Exception $ex) {
            $this->accountRepository->rollbackTransaction();
            throw $ex;
        }

        $this->logger->info('Account', [
            'operation' => __METHOD__,
            'customer_id' => $customerId,
            'balance_before' => $currentAccount,
            'balance_current' => $account->getBalance(),
            'amount' => $amount,
            'transactionId' => $transactionId
        ]);

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
            throw new NotFoundException('Not found account for customer: ' . $customerId);
        }

        return $account;
    }

    public function setBalance(int $amount, int $customerId, bool $flush = false, string $transactionId = ''): Account
    {
        if (!$transactionId) {
            $transactionId = $this->generateTransactionId($customerId);
        }

        try {
            $this->accountRepository->startTransaction();
            $account = $this->accountRepository->findLockBy($customerId);
        } catch (NotFoundException $ex) {
            $account = (new Account())
                ->setCustomer($this->userRepository->findOrThrow($customerId));
        }

        try {
            $account->setBalance($amount)
                ->addBilling((new Billing())
                    ->setCustomer($account->getCustomer())
                    ->setType(Billing::TYPE_MODIFY)
                    ->setTransactionId($transactionId)
                    ->setSystem(Billing::SYSTEM)
                    ->setDate(new DateTime('now'))
                    ->setSum($amount)
            );

            $this->accountRepository->save($account, $flush);

            if ($flush) {
                $this->accountRepository->commitTransaction();
            }
        } catch (\Exception $ex) {
            $this->accountRepository->rollbackTransaction();
            throw $ex;
        }

        return $account;
    }

    public function deposit(int $amount, int $customerId, bool $flush = false, string $transactionId = ''): Account
    {
        if (!$transactionId) {
            $transactionId = $this->generateTransactionId($customerId);
        }

        try {
            $this->accountRepository->startTransaction();
            $account = $this->accountRepository->findLockBy($customerId);
            $amount = $amount * self::DIMENSION_TOKEN;
            $currentAccount = $account->getBalance();
            $account->addBalance($amount)
                ->addBilling((new Billing())
                    ->setCustomer($account->getCustomer())
                    ->setType(Billing::TYPE_DEPOSIT)
                    ->setTransactionId($transactionId)
                    ->setSystem(Billing::SYSTEM)
                    ->setDate(new DateTime('now'))
                    ->setSum($amount)
            );

            $this->accountRepository->save($account, $flush);

            if ($flush) {
                $this->accountRepository->commitTransaction();
            }
        } catch (\Exception $ex) {
            $this->accountRepository->rollbackTransaction();
            throw $ex;
        }

        $this->logger->info('Account', [
            'operation' => __METHOD__,
            'customer_id' => $customerId,
            'balance_before' => $currentAccount,
            'balance_current' => $account->getBalance(),
            'amount' => $amount,
            'transactionId' => $transactionId
        ]);

        return $account;
    }

    private function generateTransactionId(mixed $idt): string
    {
        return Uuid::v3(Uuid::v7(), $idt);
    }
}
