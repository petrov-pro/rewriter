<?php
namespace App\Command;

use App\MessageHandler\SpreadHandler;
use App\Repository\ContextRepository;
use App\Request\Cryptonews\DTO\NewsDTO;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

#[AsCommand(
        name: 'app:spread-resend',
        description: 'Resend message to consumer',
    )]
class SpreadResendCommand extends Command
{

    public function __construct(
        private MessageBusInterface $bus,
        private ContextRepository $contextRepository
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('site_id', InputArgument::REQUIRED, 'Site id')
            ->addArgument('lang', InputArgument::REQUIRED, 'Lang')
            ->addArgument('context_ids', InputArgument::REQUIRED, 'Context ids that will resend');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $contextIds = $input->getArgument('context_ids');
        $siteId = $input->getArgument('site_id');
        $lang = $input->getArgument('lang');

        foreach (explode(",", $contextIds) as $contextId) {
            $context = $this->contextRepository->find($contextId);
            if (!$context) {
                $io->warning("Can not find context: " . $contextId);
                continue;
            }

            $message = (new NewsDTO())
                ->setId($context->getId())
                ->setSourceName($context->getSourceName())
                ->setSiteId($siteId)
                ->setTitle($context->getTitle())
                ->setLang($lang);

            $this->bus->dispatch(
                $message,
                [new TransportNamesStamp([SpreadHandler::TRANSPORT_NAME])]
            );
        }


        $io->success('Spread request send.');

        return Command::SUCCESS;
    }
}
