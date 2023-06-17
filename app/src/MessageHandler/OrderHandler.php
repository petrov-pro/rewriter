<?php
namespace App\MessageHandler;

use App\Entity\Site;
use App\Entity\User;
use App\MessageHandler\Message\ContextInterface;
use App\Messenger\Stamp\LoopCount;
use App\Repository\SiteRepository;
use App\Repository\TranslateRepository;
use App\Repository\UserRepository;
use App\Util\FetchContentPeriodTypeEnum;
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

        /** @var User $user */
        foreach ($users as $user) {
            $sites = $user->getSites()
                ->slice(0, $user->getMaxSite());

            /** @var Site $site */
            foreach ($sites as $site) {

                if (!$this->canOrder($site)) {
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

    private function canOrder(Site $site): bool
    {
        if (!$site->getFetchContent() || $site->getFetchContent() === FetchContentPeriodTypeEnum::ALWAYS->value) {
            return true;
        }

        $updateAt = $site->getUpdateAt();
        $curent = new DateTimeImmutable();
        $newUpdateAt = $updateAt->add(new DateInterval($site->getFetchContent()));

        if ($curent >= $newUpdateAt) {
            $curentCountFetch = $this->translateRepository->countBy($site->getId(), $updateAt, $curent);
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
