<?php
namespace App\MessageHandler;

use App\MessageHandler\Message\ContextInterface;
use App\Repository\SiteRepository;
use Psr\Log\LoggerInterface;

class SpreadHandler implements HanlderMessageInterface
{

    public const TRANSPORT_NAME = 'spread';

    public function __construct(
        private LoggerInterface $logger,
        private SiteRepository $siteRepository,
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

        $this->logger->info('Spread finished.');
    }
}
