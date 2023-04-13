<?php
namespace App\Command;

use App\Entity\APIToken;
use App\Entity\User;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
        name: 'app:token-create',
        description: 'Add a short description for your command',
    )]
class TokenHandlerCommand extends Command
{

    public function __construct(
        private UserRepository $user,
        private string $saltWord
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('user_email', InputArgument::REQUIRED, 'User email')
            ->addArgument('user_password', InputArgument::REQUIRED, 'User password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userEmail = $input->getArgument('user_email');
        $userPassword = $input->getArgument('user_password');

        $user = $this->user->findOneBy(['email' => $userEmail]);

        if (!$user) {
            $io->info('User not found, try to create');
            $user = (new User())->setEmail($userEmail);
        }

        $hash = md5($user->getEmail() . $this->saltWord);
        $apiToken = (new APIToken())->setIsValid(true)
            ->setDate(new DateTime('now'))
            ->setToken($hash);

        $user->addAPIToken($apiToken)
            ->setPassword($userPassword);

        $this->user->save($user, true);
        $io->success('Done. Hash: ' . $hash);

        return Command::SUCCESS;
    }
}
