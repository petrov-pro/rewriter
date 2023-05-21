<?php
namespace App\Messenger\Middleware;

use App\Messenger\Stamp\LoopCount;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandlerArgumentsStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

class ResendMiddleware implements MiddlewareInterface
{

    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (
            ($stamp = $envelope->last(LoopCount::class)) &&
            $envelope->last(ReceivedStamp::class)
        ) {
            $stamp->increaseCount();
            $envelope = $envelope->with(new HandlerArgumentsStamp([$stamp]));
        }

        return $stack->next()->handle($envelope, $stack);
    }
}
