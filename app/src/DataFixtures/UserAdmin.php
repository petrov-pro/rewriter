<?php
namespace App\DataFixtures;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AccountService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

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
            ->setLang(['en'])
            ->setContextCategory(['admin'])
            ->setRoles([User::ROLE_ADMIN])
            ->setPassword(md5(''));
        $user = $this->userRepository->addApiToken($user, 64);

        $manager->persist($user);
        $this->accountService->setBalance(0, $user->getId(), true);
        $console = new \Symfony\Component\Console\Output\ConsoleOutput();
        $console->writeln('<info>TOKEN: ' . $user->getAPITokens()[0]->getToken() . '</info>');
    }
}
