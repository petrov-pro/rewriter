<?php
namespace App\MessageHandler;

use App\Entity\Site;
use App\MessageHandler\Message\ContextInterface;
use App\Messenger\Stamp\LoopCount;
use App\Repository\SiteRepository;
use App\Repository\TranslateRepository;
use App\Repository\UserRepository;
use DateInterval;
use DateTimeImmutable;
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
        private SiteRepository $siteRepository,
        private TranslateRepository $translateRepository,
        private array $availableLangs,
        private bool $needCreateImage,
        private int $countRepeatRewriteMax,
        private int $newsItemCount
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
            /** @var Site $site */
            foreach ($user->getSites() as $site) {

                if (!$this->canOrder($site, $message->getId())) {
                    continue;
                }

                $message->setUserId($user->getId())
                    ->setSiteId($site->getId());

                foreach ($site->getLang() as $lang) {
                    if (!Arrays::contains($this->availableLangs, $lang)) {
                        $this->logger->warning('Skip unsupported lang: ' . $lang);
                        continue;
                    }

                    $this->bus->dispatch(
                        $message->setLang($lang),
                        [
                            new TransportNamesStamp([RewriteHandler::TRANSPORT_NAME]),
                            new LoopCount(
                                $this->getCountRepeat($site->getCountRepeat())
                            )
                        ]
                    );
                }

                if ($this->needCreateImage && $site->isImage()) {
                    $this->bus->dispatch(
                        $message,
                        [new TransportNamesStamp([ImageHandler::TRANSPORT_NAME])]
                    );
                }
            }
        }

        $this->logger->info('Order finished.');
    }

    private function getCountRepeat(?int $countRepeat): int
    {
        if (!$countRepeat || $countRepeat >= $this->countRepeatRewriteMax) {
            return $this->countRepeatRewriteMax;
        }

        return $countRepeat;
    }

    private function canOrder(Site $site, int $contextId): bool
    {
        if (!$site->getFetchContent()) {
            return true;
        }

        $updateAt = $site->getUpdateAt();
        $curent = new DateTimeImmutable();

        $curentCountFetch = $this->translateRepository->countBy($contextId, $site->getId(), $updateAt, $curent);

        if ($curent >= $updateAt->add(new DateInterval($site->getFetchContent()))) {
            if ($this->newsItemCount >= $curentCountFetch) {
                return true;
            }

            $site->setUpdateAt(new DateTimeImmutable());
            $this->siteRepository->save($site, true);
            return false;
        }

        return false;
    }
}
