<?php
namespace App\MessageHandler;

use App\MessageHandler\Message\ContextInterface;
use App\Repository\ContextRepository;
use App\Repository\SiteRepository;
use App\Service\Spread\SpreadProviderFactory;
use Psr\Log\LoggerInterface;

class SpreadHandler implements HanlderMessageInterface
{

    public const TRANSPORT_NAME = 'spread';

    public function __construct(
        private LoggerInterface $logger,
        private SiteRepository $siteRepository,
        private SpreadProviderFactory $spreadProviderFactory,
        private ContextRepository $contextRepository
    )
    {
        
    }

    public function handle(ContextInterface $message): void
    {
        $this->logger->info('Spread get content message',
            [
                'source' => $message->getSourceName(),
                'title' => $message->getTitle()
            ]
        );

        $site = $this->siteRepository->find($message->getSiteId());

        if (!$site->isSend()) {
            $this->logger->info('Site has disable option is_sent: ' . $site->getId());

            return;
        }

        if (!$site->getSetting()) {
            $this->logger->info('Site has option is_sent but empty settings: ' . $site->getId());

            return;
        }

        $context = $this->contextRepository->findByIdLang($message->getId(), $site->getId(), $message->getLang());

        if ($site->isImage() && $context->getImages()->isEmpty()) {
            throw new \Exception('Should wait, not found image for site: ' . $site->getId());
        }

        if ($context->getTranslates()->isEmpty()) {
            throw new \Exception('Should wait, not found translate for site: ' . $site->getId());
        }


        $spreadProvider = $this->spreadProviderFactory->create($site->getType());
        $spreadProvider->spread($context, $site);

        $this->logger->info('Spread finished.');
    }
}
