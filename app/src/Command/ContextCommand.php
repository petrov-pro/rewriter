<?php
namespace App\Command;

use App\MessageHandler\SourceHandler;
use App\Request\Cryptonews\DTO\NewsDTO;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

#[AsCommand(name: 'app:context')]
class ContextCommand extends Command
{

    public function __construct(
        private MessageBusInterface $bus
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
        //send empty message for start flow
        $this->bus->dispatch(
            new NewsDTO(),
            [new TransportNamesStamp([SourceHandler::TRANSPORT_NAME])]
        );

        return Command::SUCCESS;
    }
}
