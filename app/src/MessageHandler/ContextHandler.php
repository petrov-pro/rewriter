<?php
namespace App\MessageHandler;

use App\MessageHandler\Message\ContextInterface;
use App\Service\ImageHandler;
use App\Service\Parser\ParserHandler;
use App\Service\RewriteHandler;
use App\Service\SpreadHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class ContextHandler
{

    public function __construct(
        private ParserHandler $parserService,
        private RewriteHandler $rewriteService,
        private SpreadHandler $spreadService,
        private ImageHandler $imageService
    )
    {
        
    }

    #[AsMessageHandler(fromTransport: ParserHandler::TRANSPORT_NAME)]
    public function handleParse(ContextInterface $message)
    {
        $this->parserService->handle($message);
    }

    #[AsMessageHandler(fromTransport: SpreadHandler::TRANSPORT_NAME)]
    public function handleSpread(ContextInterface $message)
    {
        $this->spreadService->handle($message);
    }

    #[AsMessageHandler(fromTransport: ImageHandler::TRANSPORT_NAME)]
    public function handleImage(ContextInterface $message)
    {
        $this->imageService->handle($message);
    }

    #[AsMessageHandler(fromTransport: RewriteHandler::TRANSPORT_NAME)]
    public function handleRewrite(ContextInterface $message)
    {
        $this->rewriteService->handle($message);
    }
}
