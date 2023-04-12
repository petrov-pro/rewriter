<?php
namespace App\MessageHandler;

use App\MessageHandler\Message\ContextInterface;
use App\Service\Parser\ParserService;
use App\Service\RewriteService;
use App\Service\SpreadService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class ContextHandler
{

    public function __construct(
        private ParserService $parserService,
        private RewriteService $rewriteService,
        private SpreadService $spreadService
    )
    {
        
    }

    #[AsMessageHandler(fromTransport: ParserService::TRANSPORT_NAME)]
    public function handleParse(ContextInterface $message)
    {
        $this->parserService->handle($message);
    }

    #[AsMessageHandler(fromTransport: RewriteService::TRANSPORT_NAME)]
    public function handleRewrite(ContextInterface $message)
    {
        $this->rewriteService->handle($message);
    }

    #[AsMessageHandler(fromTransport: SpreadService::TRANSPORT_NAME)]
    public function handleSpread(ContextInterface $message)
    {
        $this->spreadService->handle($message);
    }
}
