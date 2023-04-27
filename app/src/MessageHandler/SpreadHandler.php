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

        $context = $this->contextRepository->findByIdLang($message->getId(), $message->getLang());

        if ($site->isImage() && !$context->getImages()) {
            throw new \Exception('Should wait, not found image for stie: ' . $site->getId());
        }

        $spreadProvider = $this->spreadProviderFactory->create($site->getType());
        $spreadProvider->spread($site->getSetting(), $context);

        $this->logger->info('Spread finished.');
    }
}
