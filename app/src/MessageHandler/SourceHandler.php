<?php
namespace App\MessageHandler;

use App\MessageHandler\Message\ContextInterface;
use App\Service\ContextProvider\ContextProviderInterface;
use App\Service\ContextService;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

class SourceHandler
{

    public const TRANSPORT_NAME = 'source';

    /**
     * @param ContextProviderInterface[] $contextProviders
     */
    public function __construct(
        private LoggerInterface $logger,
        private MessageBusInterface $bus,
        private iterable $contextProviders,
        private ContextService $contextService
    )
    {
        
    }

    public function handle(): void
    {
        foreach ($this->contextProviders as $contextProvider) {
            $this->logger->info('Start to get content from: ' . $contextProvider->getProviderName());
            try {
                /** @var ContextProviderInterface $contextProvider */
                $contexts = $contextProvider->getContexts();
                /** @var ContextInterface $message */
                foreach ($contexts as $message) {
                    if ($this->contextService->isDuplicate($message->getTitle(), $message->getSourceName())) {
                        $this->logger->info('Find duplicate: ' . $contextProvider->getProviderName() . ':' . $message->getSourceName());
                        continue;
                    }

                    $message->setProvider($contextProvider->getProviderName());
                    $contextEntity = $this->contextService->create($message);
                    $message->setId($contextEntity->getId());

                    $this->logger->info('Start to send content from: ' . $contextProvider->getProviderName() . ':' . $message->getSourceName());
                    $this->bus->dispatch($message,
                        [new TransportNamesStamp([ParserHandler::TRANSPORT_NAME])]
                    );
                }
            } catch (Exception $ex) {
                $this->logger->error($ex->getMessage(), (array) $ex);
            }
        }
        $this->logger->info('End to handle content.');
    }
}
