<?php
namespace App\MessageHandler;

use App\MessageHandler\Message\ContextInterface;
use App\Repository\SiteRepository;
use App\Service\AccountService;
use App\Service\AI\AIInterface;
use App\Service\AI\TooMuchTokenException;
use App\Service\ContextService;
use App\Util\APIEnum;
use App\Util\HtmlTagEnum;
use App\Util\TypeDataEnum;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class RewriteHandler implements HanlderMessageInterface
{

    public const TRANSPORT_NAME = 'rewrite';

    public function __construct(
        private ContextService $contextService,
        private AIInterface $AIService,
        private AccountService $accountService,
        private SiteRepository $siteRepository,
        private LoggerInterface $logger,
        private MessageBusInterface $bus,
        private TagAwareCacheInterface $cache,
        private int $countRepeatRewrite
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

            if ($this->contextService->isDuplicateTranslate($message->getUserId(), $message->getId(), $message->getSiteId(), $message->getLang())) {
                $this->logger->info('Rewriter skip duplicate', [
                    'message_id' => $message->getId(),
                    'user_id' => $message->getUserId(),
                    'lang' => $message->getLang(),
                ]);
                $this->sendMessageToSpread($message);

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

            $message = $this->rewriteProcess($message);

            if ($message->getCountRewrite() >= $this->countRepeatRewrite) {

                $this->saveContext($message);
                $this->logger->info('Rewriter finished content message',
                    [
                        'source' => $message->getSourceName(),
                        'title' => $message->getTitle()
                    ]
                );
                $this->sendMessageToSpread($message);

                return;
            }

            $message->setCountRewrite($message->getCountRewrite() + 1);
            $this->logger->info('Rewriter send again content message: ' . $message->getCountRewrite(),
                [
                    'source' => $message->getSourceName(),
                    'title' => $message->getTitle()
                ]
            );
            $this->bus->dispatch(
                $message,
                [new TransportNamesStamp([RewriteHandler::TRANSPORT_NAME])]
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

    private function sendMessageToSpread(ContextInterface $message): void
    {
        $this->cache->invalidateTags([APIEnum::CACHE_TAG_USER->value . $message->getUserId()]);
        $this->bus->dispatch(
            $message,
            [new TransportNamesStamp([SpreadHandler::TRANSPORT_NAME])]
        );
    }

    private function rewriteProcess(ContextInterface $message): ContextInterface
    {
        $translateLang = ($message->getLang() !== $message->getOriginalLang()) ? $message->getLang() : '';
        $site = $this->siteRepository->find($message->getSiteId());
        $text = $this->AIService->rewrite(
            $message->getUserId(),
            ($site->getHtmlTag() === HtmlTagEnum::TAG_NOT_USE->value) ? strip_tags($message->getText()) : $message->getText(),
            $message->getOriginalLang(),
            $translateLang,
            $site->getHtmlTag()
        );
        $textDescription = $this->AIService->rewrite($message->getUserId(), $message->getDescription(), $message->getOriginalLang(), $translateLang, HtmlTagEnum::TAG_NOT_USE->value);
        $textTitle = $this->AIService->rewrite($message->getUserId(), $message->getTitle(), $message->getOriginalLang(), $translateLang, HtmlTagEnum::TAG_NOT_USE->value);
        $token = ($text->getToken() + $textDescription->getToken() + $textTitle->getToken()) + $message->getToken();

        return $message->setTitle($textTitle->getText())
                ->setDescription($textDescription->getText())
                ->setText($text->getText())
                ->setOriginalLang($message->getLang())
                ->setToken($token);
    }

    private function saveContext(ContextInterface $message): void
    {
        ///transactional
        $this->contextService->saveModifyContext(
            $message->getId(),
            $message->getUserId(),
            $message->getSiteId(),
            $message->getText(),
            $message->getDescription(),
            $message->getTitle(),
            $message->getLang(),
            $message->getToken(),
            false
        );

        $this->accountService->withdraw(
            $this->AIService->findCost(
                TypeDataEnum::TEXT,
                $message->getToken()
            ),
            $message->getUserId(),
            true
        );
    }
}
