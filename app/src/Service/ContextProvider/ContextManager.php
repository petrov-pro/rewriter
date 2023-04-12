<?php
namespace App\Service\ContextProvider;

use App\MessageHandler\Message\ContextInterface;
use App\Service\ContextService;
use App\Service\Parser\ParserService;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

class ContextManager
{

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
                    if ($this->contextService->isDuplicate($message->getTitle())) {
                        $this->logger->info('Find duplicate: ' . $contextProvider->getProviderName() . ':' . $message->getSourceName());
                        continue;
                    }

                    $contextEntity = $this->contextService->create($message);
                    $message->setId($contextEntity->getId());

                    $this->logger->info('Start to send content from: ' . $contextProvider->getProviderName() . ':' . $message->getSourceName());
                    $this->bus->dispatch($message,
                        [new TransportNamesStamp([ParserService::TRANSPORT_NAME])]
                    );
                }
            } catch (Exception $ex) {
                $this->logger->error($ex->getMessage(), (array) $ex);
            }
        }
        $this->logger->info('End to handle content.');
    }
}
