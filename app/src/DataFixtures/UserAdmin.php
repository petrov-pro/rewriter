<?php
namespace App\DataFixtures;

use App\Entity\User;
use App\Service\AccountService;
use Doctrine\Persistence\ObjectManager;
use Nette\Utils\Random;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserAdmin
{

    public function __construct(
        private AccountService $accountService,
        private UserPasswordHasherInterface $passwordHasher,
        private string $adminEmail
    )
    {
        
    }

    public function load(ObjectManager $manager): void
    {

        $user = (new User())->setEmail($this->adminEmail)
            ->setRoles([User::ROLE_ADMIN])
            ->setCompany('Admin company')
            ->setMaxSite(0)
            ->addQuickAPIToken(64);

        $password = Random::generate(12);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $manager->persist($user);
        $this->accountService->setBalance(0, $user->getId(), true);
        $console = new ConsoleOutput();
        $console->writeln('<info>TOKEN: ' . $user->getAPITokens()[0]->getToken() . ' Password: ' . $password . '</info>');
    }
}
