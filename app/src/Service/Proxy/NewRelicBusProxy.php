<?php
namespace App\Service\Proxy;

use App\MessageHandler\Message\ContextInterface;
use Arxus\NewrelicMessengerBundle\Newrelic\NameableNewrelicTransactionInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

class NewRelicBusProxy implements MessageBusInterface
{

    public function __construct(private MessageBusInterface $busExternal)
    {
        
    }

    public function dispatch(mixed $message, array $stamps = []): Envelope
    {
        if (
            $message instanceof NameableNewrelicTransactionInterface && $message instanceof ContextInterface
        ) {
            foreach ($stamps as $stamp) {
                if ($stamp instanceof TransportNamesStamp) {
                    $message->transationName = $stamp->getTransportNames()[0];
                }
            }
        }

        return $this->busExternal->dispatch($message, $stamps);
    }
}
