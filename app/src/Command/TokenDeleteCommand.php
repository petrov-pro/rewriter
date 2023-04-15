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
        name: 'app:token-delete',
        description: 'Delete user token',
    )]
class TokenDeleteCommand extends Command
{

    public function __construct(
        private UserRepository $user
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('user_email', InputArgument::REQUIRED, 'User email')
            ->addArgument('user_token', InputArgument::OPTIONAL, 'User token');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userEmail = $input->getArgument('user_email');
        $userToken = $input->getArgument('user_token');

        $user = $this->user->findOneBy(['email' => $userEmail]);

        if (!$user) {
            $io->error('User not found');
            return Command::FAILURE;
        }

        foreach ($user->getAPITokens() as $apiToken) {
            if ($userToken && $userToken === $apiToken->getHash()) {
                $user->removeAPIToken($apiToken);
                break;
            } else if (!$userToken) {
                $user->removeAPIToken($apiToken);
            }
        }
        $this->user->save($user, true);

        $io->success('Done.');

        return Command::SUCCESS;
    }
}
