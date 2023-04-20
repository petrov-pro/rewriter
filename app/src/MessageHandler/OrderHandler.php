<?php
namespace App\MessageHandler;

use App\MessageHandler\Message\ContextInterface;
use App\Repository\UserRepository;
use Nette\Utils\Arrays;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

class OrderHandler implements HanlderMessageInterface
{

    public const TRANSPORT_NAME = 'spread';

    public function __construct(
        private LoggerInterface $logger,
        private UserRepository $userRepository,
        private MessageBusInterface $bus,
        private array $availableLangs,
        private bool $needCreateImage
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
        $users = $this->userRepository->findAllActive($message->getCategory());
        
        foreach ($users as $user) {
            foreach ($user->getLang() as $lang) {
                if (!Arrays::contains($this->availableLangs, $lang)) {
                    $this->logger->warning('Skip unsupported lang: ' . $lang);
                    continue;
                }

                $message->setLang($lang)
                    ->setUserId($user->getId());

                $this->bus->dispatch(
                    $message,
                    [new TransportNamesStamp([RewriteHandler::TRANSPORT_NAME])]
                );
            }

            if ($this->needCreateImage) {
                $this->bus->dispatch(
                    $message,
                    [new TransportNamesStamp([ImageHandler::TRANSPORT_NAME])]
                );
            }
        }

        $this->logger->info('Spread finished.');
    }
}
