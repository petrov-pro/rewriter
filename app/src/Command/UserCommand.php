<?php
namespace App\Command;

use App\DataFixtures\UserAdmin;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
        name: 'app:user',
        description: 'User operations',
    )]
class UserCommand extends Command
{

    public function __construct(
        private ManagerRegistry $manager,
        private UserAdmin $fixture
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Create default admin user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('admin')) {
            $this->fixture->load($this->manager->getManager());
        }
        $io->success('Opertion completed');

        return Command::SUCCESS;
    }
}
