<?php
namespace App\Service\Parser;

use App\MessageHandler\Message\ContextInterface;
use App\Service\AI\OpenAIService;
use App\Service\ContextService;
use App\Service\HanlderMessageInterface;
use App\Util\NormalizeText;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

class ParserService implements HanlderMessageInterface
{

    public const TRANSPORT_NAME = 'parse';

    public function __construct(
        private LoggerInterface $logger,
        private MessageBusInterface $bus,
        private ParserFactory $parserFactory,
        private ContextService $contextService
    )
    {
        
    }

    public function handle(ContextInterface $message): void
    {
        try {
            $this->logger->info('Get content message',
                [
                    'source' => $message->getSourceName(),
                    'title' => $message->getTitle()
                ]
            );

            $siteParser = $this->parserFactory->create($message->getSourceName());
            $fullText = $siteParser->parser($message->getSourceUrl());
            $message->setText(NormalizeText::handle($fullText));
            $this->logger->info('Sent to rewrite q content message',
                [
                    'source' => $message->getSourceName(),
                    'title' => $message->getTitle()
                ]
            );
            $this->bus->dispatch(
                $message,
                [new TransportNamesStamp([OpenAIService::TRANSPORT_NAME])]
            );
        } catch (NotFoundParserException $ex) {
            $this->contextService->updateStatus($message->getId(), ContextService::STATUS_PARSER_NOT_FOUND);
            $this->logger->info($ex->getMessage());
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage(), (array) $ex);
            throw $ex;
        }
    }
}
