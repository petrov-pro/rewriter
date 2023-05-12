<?php
namespace App\MessageHandler;

use App\MessageHandler\Message\ContextInterface;
use App\Messenger\Stamp\LoopCount;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class MessageHandler
{

    public function __construct(
        private ParserHandler $parserHandler,
        private RewriteHandler $rewriteHandler,
        private SpreadHandler $spreadHandler,
        private ImageHandler $imageHandler,
        private SourceHandler $contextHandler,
        private OrderHandler $orderHandler
    )
    {
        
    }

    #[AsMessageHandler(fromTransport: SourceHandler::TRANSPORT_NAME)]
    public function handleSource(ContextInterface $message)
    {
        $this->contextHandler->handle();
    }

    #[AsMessageHandler(fromTransport: ParserHandler::TRANSPORT_NAME)]
    public function handleParse(ContextInterface $message)
    {
        $this->parserHandler->handle($message);
    }

    #[AsMessageHandler(fromTransport: OrderHandler::TRANSPORT_NAME)]
    public function handleOrder(ContextInterface $message)
    {
        $this->orderHandler->handle($message);
    }

    #[AsMessageHandler(fromTransport: ImageHandler::TRANSPORT_NAME)]
    public function handleImage(ContextInterface $message)
    {
        $this->imageHandler->handle($message);
    }

    #[AsMessageHandler(fromTransport: RewriteHandler::TRANSPORT_NAME)]
    public function handleRewrite(ContextInterface $message, LoopCount $loopCount)
    {
        $this->rewriteHandler
            ->setLoopCount($loopCount)
            ->handle($message);
    }

    #[AsMessageHandler(fromTransport: SpreadHandler::TRANSPORT_NAME)]
    public function handleSpread(ContextInterface $message)
    {
        $this->spreadHandler->handle($message);
    }
}
