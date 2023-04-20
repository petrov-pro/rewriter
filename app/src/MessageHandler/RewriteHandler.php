<?php
namespace App\MessageHandler;

use App\MessageHandler\Message\ContextInterface;
use App\Repository\UserRepository;
use App\Service\AccountService;
use App\Service\AI\AIInterface;
use App\Service\AI\TooMuchTokenException;
use App\Service\ContextService;
use App\Util\APIEnum;
use App\Util\HtmlTagEnum;
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
        private TagAwareCacheInterface $cache,
        private UserRepository $userRepository
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

            $this->rewriteProcess($message);

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

    private function rewriteProcess(ContextInterface $message): void
    {
        $user = $this->userRepository->findOrThrow($message->getUserId());
        $text = $this->AIService->rewrite(
            $message->getUserId(),
            ($user->getHtmlTag() === HtmlTagEnum::TAG_NOT_USE->value) ? strip_tags($message->getText()) : $message->getText(),
            $message->getLang(),
            $user->getHtmlTag()
        );
        $textDescription = $this->AIService->rewrite($message->getUserId(), $message->getDescription(), $message->getLang(), HtmlTagEnum::TAG_NOT_USE->value);
        $textTitle = $this->AIService->rewrite($message->getUserId(), $message->getTitle(), $message->getLang(), HtmlTagEnum::TAG_NOT_USE->value);
        $token = $text->getToken() + $textDescription->getToken() + $textTitle->getToken();

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
    }
}
