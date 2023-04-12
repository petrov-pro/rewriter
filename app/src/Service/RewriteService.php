<?php
namespace App\Service;

use App\MessageHandler\Message\ContextInterface;
use App\Service\AI\AIInterface;
use App\Service\AI\TooMuchTokenException;
use App\Util\NormalizeText;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

class RewriteService implements HanlderMessageInterface
{

    public const TRANSPORT_NAME = 'rewrite';

    public function __construct(
        private ContextService $contextService,
        private AIInterface $AIService,
        private LoggerInterface $logger,
        private MessageBusInterface $bus,
        private array $availableLangs,
        private bool $needCreateImage
    )
    {
        
    }

    public function handle(ContextInterface $message): void
    {
        try {
            foreach ($this->availableLangs as $lang) {
                $this->logger->info('Get content message',
                    [
                        'source' => $message->getSourceName(),
                        'title' => $message->getTitle(),
                        'lang' => $message->getLang()
                    ]
                );

                $langTranslate = $lang;
                if ($lang === $message->getLang()) {
                    $langTranslate = '';
                }

                $text = $this->AIService->rewrite($message->getText(), $langTranslate);
                $textDescription = $this->AIService->rewrite($message->getDescription(), $langTranslate);
                $textTitle = $this->AIService->rewrite($message->getTitle(), $langTranslate);

                if (!$text || !$textDescription || !$textTitle) {
                    throw new InvalidArgumentException('Text modify is empty');
                }

                $this->contextService->saveModifyContext($message->getId(), $text, $textDescription, $textTitle, $lang);
            }

            $imageUrl = '';
            if ($this->needCreateImage) {
                $keywords = $this->AIService->keywords($message->getTitle());
                $imageUrl = $this->AIService->createImage(NormalizeText::handle($keywords));
            }

            $this->contextService->saveModifyContext(
                $message->getId(),
                $message->getText(),
                $message->getDescription(),
                $message->getTitle(),
                $message->getLang(),
                $imageUrl,
                ContextService::TYPE_ORIGINAL,
                ContextService::STATUS_FINISH
            );

            $this->logger->info('Sent to spread q content message',
                [
                    'source' => $message->getSourceName(),
                    'title' => $message->getTitle()
                ]
            );

            $this->bus->dispatch(
                $message,
                [new TransportNamesStamp([SpreadService::TRANSPORT_NAME])]
            );
        } catch (TooMuchTokenException $ex) {
            $this->logger->info($ex->getMessage(), [
                'message_id' => $message->getId()
            ]);
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage(), (array) $ex);
            throw $ex;
        }
    }
}
