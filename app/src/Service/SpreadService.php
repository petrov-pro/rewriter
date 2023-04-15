<?php
namespace App\Service;

use App\Controller\APIController;
use App\MessageHandler\Message\ContextInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class SpreadService implements HanlderMessageInterface
{

    public const TRANSPORT_NAME = 'spread';

    public function __construct(
        private LoggerInterface $logger,
        private TagAwareCacheInterface $cache
    )
    {
        
    }

    //Something for additional processing. Example: save image, send to external server, etc
    public function handle(ContextInterface $message): void
    {

        $this->cache->invalidateTags([APIController::CACHE_TAG]);
        $this->logger->info('Spread finished.');
    }
}
