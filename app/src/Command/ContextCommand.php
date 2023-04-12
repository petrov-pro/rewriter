<?php
namespace App\Command;

use App\Service\ContextProvider\ContextManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:context')]
class ContextCommand extends Command
{

    public function __construct(
        private ContextManager $manager
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Start</info>');
        $response = $this->manager->handle();
        $output->writeln("<info>$response</info>");

        return Command::SUCCESS;
    }
}
