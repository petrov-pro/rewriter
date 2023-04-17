<?php
namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
        name: 'app:token-list',
        description: 'List all avialable user token',
    )]
class TokenListCommand extends Command
{

    public function __construct(
        private UserRepository $user
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('user_email', InputArgument::REQUIRED, 'User email');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userEmail = $input->getArgument('user_email');

        $user = $this->user->findOneBy(['email' => $userEmail]);

        if (!$user) {
            $io->error('User not found');
            return Command::FAILURE;
        }

        $io->newLine();
        $table = new Table($output);
        $table->setHeaders(['Token', 'Lang', 'Category', 'Balance', 'Date']);
        $table->setHeaderTitle('User: ' . $user->getEmail());

        foreach ($user->getAPITokens() as $token) {
            $table->addRow([
                $token->getToken(),
                implode(',', $token->getCustomer()->getLang()),
                implode(',', $token->getCustomer()->getContextCategory()),
                $token->getCustomer()->getAccount()->getBalance(),
                $token->getDate()->format('d/m/Y')
            ]);
        }
        $table->render();

        $io->success('Done.');

        return Command::SUCCESS;
    }
}
