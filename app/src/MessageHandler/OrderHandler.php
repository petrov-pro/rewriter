<?php
namespace App\MessageHandler;

use App\MessageHandler\Message\ContextInterface;
use App\Messenger\Stamp\LoopCount;
use App\Repository\UserRepository;
use Nette\Utils\Arrays;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

class OrderHandler implements HanlderMessageInterface
{

    public const TRANSPORT_NAME = 'order';

    public function __construct(
        private LoggerInterface $logger,
        private UserRepository $userRepository,
        private MessageBusInterface $bus,
        private array $availableLangs,
        private bool $needCreateImage,
        private int $countRepeatRewrite
    )
    {
        
    }

    public function handle(ContextInterface $message): void
    {
        $this->logger->info('Order get content message',
            [
                'source' => $message->getSourceName(),
                'title' => $message->getTitle()
            ]
        );
        $users = $this->userRepository->findAllActive($message->getCategory());

        foreach ($users as $user) {
            foreach ($user->getSites() as $site) {
                foreach ($site->getLang() as $lang) {
                    if (!Arrays::contains($this->availableLangs, $lang)) {
                        $this->logger->warning('Skip unsupported lang: ' . $lang);
                        continue;
                    }

                    $message->setLang($lang)
                        ->setUserId($user->getId())
                        ->setSiteId($site->getId());

                    $this->bus->dispatch(
                        $message,
                        [
                            new TransportNamesStamp([RewriteHandler::TRANSPORT_NAME]),
                            new LoopCount($this->countRepeatRewrite)
                        ]
                    );
                }
            }

            if ($this->needCreateImage) {
                $this->bus->dispatch(
                    $message,
                    [new TransportNamesStamp([ImageHandler::TRANSPORT_NAME])]
                );
            }
        }

        $this->logger->info('Order finished.');
    }
}
