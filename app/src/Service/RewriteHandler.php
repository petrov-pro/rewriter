<?php
namespace App\Service;

use App\MessageHandler\Message\ContextInterface;
use App\Service\AI\AIInterface;
use App\Service\AI\TooMuchTokenException;
use App\Util\APIEnum;
use App\Util\TypeDataEnum;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class RewriteHandler implements HanlderMessageInterface
{

    public const TRANSPORT_NAME = 'rewrite';

    public function __construct(
        private ContextService $contextService,
        private AIInterface $AIService,
        private AccountService $accountService,
        private LoggerInterface $logger,
        private TagAwareCacheInterface $cache
    )
    {
        
    }

    public function handle(ContextInterface $message): void
    {
        try {
            $this->logger->info('Rewriter get content message',
                [
                    'source' => $message->getSourceName(),
                    'title' => $message->getTitle(),
                    'lang' => $message->getLang()
                ]
            );

            if ($this->contextService->isDuplicateTranslate($message->getUserId(), $message->getId(), $message->getLang())) {
                $this->logger->info('Rewriter skip duplicate', [
                    'message_id' => $message->getId(),
                    'user_id' => $message->getUserId(),
                    'lang' => $message->getLang(),
                ]);

                return;
            }

            if (!$this->accountService->isEnoughBalance(
                    $message->getUserId(),
                    $this->AIService->findSupposedCost(
                        TypeDataEnum::TEXT,
                        $message->getText() . ' ' . $message->getTitle() . ' ' . $message->getDescription()
                    )
                )
            ) {
                $this->logger->warning('Rewriter skip content message because balance not enough',
                    [
                        'customer_id' => $message->getUserId(),
                        'source' => $message->getSourceName(),
                        'title' => $message->getTitle(),
                        'lang' => $message->getLang()
                    ]
                );

                return;
            }

            $text = $this->AIService->rewrite($message->getUserId(), $message->getText(), $message->getLang());
            $textDescription = $this->AIService->rewrite($message->getUserId(), $message->getDescription(), $message->getLang());
            $textTitle = $this->AIService->rewrite($message->getUserId(), $message->getTitle(), $message->getLang());
            $token = $text->getCost() + $textDescription->getCost() + $textTitle->getCost();

            ///transactional
            $this->contextService->saveModifyContext(
                $message->getId(),
                $message->getUserId(),
                $text->getText(),
                $textDescription->getText(),
                $textTitle->getText(),
                $message->getLang(),
                $token,
                false
            );

            $this->accountService->withdraw(
                $this->AIService->findCost(
                    TypeDataEnum::TEXT,
                    $token
                ),
                $message->getUserId(),
                true
            );

            $this->logger->info('Rewriter finished content message',
                [
                    'source' => $message->getSourceName(),
                    'title' => $message->getTitle()
                ]
            );

            $this->cache->invalidateTags([APIEnum::CACHE_TAG_USER->value . $message->getUserId()]);
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
