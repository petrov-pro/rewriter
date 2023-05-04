<?php
namespace App\MessageHandler;

use App\Entity\Context;
use App\MessageHandler\Message\ContextInterface;
use App\Service\ContextService;
use App\Service\Parser\NotFoundParserException;
use App\Service\Parser\NotWantParserException;
use App\Service\Parser\ParserFactory;
use App\Service\Thief\ThiefInterface;
use App\Util\NormalizeText;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

class ParserHandler implements HanlderMessageInterface
{

    public const TRANSPORT_NAME = 'parse';

    public function __construct(
        private LoggerInterface $logger,
        private MessageBusInterface $bus,
        private ParserFactory $parserFactory,
        private ContextService $contextService,
        private ThiefInterface $capture
    )
    {
        
    }

    public function handle(ContextInterface $message): void
    {
        try {
            $this->logger->info('Parser get content message',
                [
                    'source' => $message->getSourceName(),
                    'title' => $message->getTitle()
                ]
            );

            $siteParser = $this->parserFactory->create($message->getSourceName());
            $sourceData = $this->capture->getData($message->getSourceUrl());
            $fullText = $siteParser->parser($sourceData);

            $message->setText(NormalizeText::handle($fullText));
            $this->contextService->updateStatusText($message->getId(), Context::STATUS_FINISH, $message->getText());

            $this->bus->dispatch(
                $message,
                [new TransportNamesStamp([OrderHandler::TRANSPORT_NAME])]
            );

            $this->logger->info('Parser finished content message',
                [
                    'source' => $message->getSourceName(),
                    'title' => $message->getTitle()
                ]
            );
        } catch (NotFoundParserException $ex) {
            $this->contextService->updateStatus($message->getId(), Context::STATUS_NOT_FOUND);
            $this->logger->info($ex->getMessage());
        } catch (NotWantParserException $ex) {
            $this->contextService->updateStatus($message->getId(), Context::STATUS_NOT_WANT);
            $this->logger->info($ex->getMessage());
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage(), (array) $ex);
            throw $ex;
        }
    }
}
