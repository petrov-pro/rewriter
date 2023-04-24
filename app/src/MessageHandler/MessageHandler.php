<?php
namespace App\MessageHandler;

use App\MessageHandler\Message\ContextInterface;
use stdClass;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class MessageHandler
{

    public function __construct(
        private ParserHandler $parserHandler,
        private RewriteHandler $rewriteHandler,
        private SpreadHandler $spreadHandler,
        private ImageHandler $imageHandler,
        private ContextHandler $contextHandler,
        private OrderHandler $orderHandler
    )
    {
        
    }

    #[AsMessageHandler(fromTransport: ContextHandler::TRANSPORT_NAME)]
    public function handleSource(stdClass $message)
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
    public function handleRewrite(ContextInterface $message)
    {
        $this->rewriteHandler->handle($message);
    }

    #[AsMessageHandler(fromTransport: SpreadHandler::TRANSPORT_NAME)]
    public function handleSpread(ContextInterface $message)
    {
        $this->spreadHandler->handle($message);
    }
}
