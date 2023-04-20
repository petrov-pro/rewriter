<?php
namespace App\DataFixtures;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AccountService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Output\ConsoleOutput;

class UserAdmin extends Fixture
{

    public function __construct(
        private AccountService $accountService,
        private UserRepository $userRepository,
        private string $adminEmail
    )
    {
        
    }

    public function load(ObjectManager $manager): void
    {
        $user = (new User())->setEmail($this->adminEmail)
            ->setRoles([User::ROLE_ADMIN])
            ->setPassword(md5(''))
            ->setCompany('Admin company')
            ->addQuickAPIToken(64);

        $manager->persist($user);
        $this->accountService->setBalance(0, $user->getId(), true);
        $console = new ConsoleOutput();
        $console->writeln('<info>TOKEN: ' . $user->getAPITokens()[0]->getToken() . '</info>');
    }
}
