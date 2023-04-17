<?php
namespace App\Service;

use App\Entity\Image;
use App\MessageHandler\Message\ContextInterface;
use App\Repository\ImageRepository;
use App\Repository\UserRepository;
use App\Service\AI\AIInterface;
use App\Util\APIEnum;
use App\Util\NormalizeText;
use App\Util\TypeDataEnum;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ImageHandler implements HanlderMessageInterface
{

    public const TRANSPORT_NAME = 'image';

    public function __construct(
        private AIInterface $AIService,
        private LoggerInterface $logger,
        private ImageRepository $imageRepository,
        private UserRepository $userRepository,
        private ContextService $contextService,
        private TagAwareCacheInterface $cache,
        private AccountService $accountService,
        private bool $needCreateImage,
        private int $countImage
    )
    {
        
    }

    public function handle(ContextInterface $message): void
    {
        try {
            $this->logger->info('Image get content message',
                [
                    'source' => $message->getSourceName(),
                    'title' => $message->getTitle(),
                    'lang' => $message->getLang()
                ]
            );

            if (!$this->needCreateImage) {
                $this->logger->info('Image skip content message',
                    [
                        'source' => $message->getSourceName(),
                        'title' => $message->getTitle(),
                        'lang' => $message->getLang()
                    ]
                );

                return;
            }

            if (!$this->accountService->isEnoughBalance(
                    $message->getUserId(),
                    $this->AIService->findSupposedCost(
                        TypeDataEnum::IMAGE,
                        $this->countImage
                    )
                )
            ) {
                $this->logger->warning('Image skip content message because balance not enough',
                    [
                        'customer_id' => $message->getUserId(),
                        'source' => $message->getSourceName(),
                        'title' => $message->getTitle(),
                        'lang' => $message->getLang()
                    ]
                );

                return;
            }

            $context = $this->contextService->findById($message->getId());

            if ($this->imageRepository->findOneBy([
                    'customer' => $message->getUserId(),
                    'context' => $message->getId()
                ])) {
                $this->logger->warning('Image skip content message',
                    [
                        'source' => $message->getSourceName(),
                        'title' => $message->getTitle(),
                    ]
                );

                return;
            }

            $keywords = $this->AIService->keywords($message->getUserId(), $message->getTitle());
            $imageAI = $this->AIService->createImage($message->getUserId(), NormalizeText::handle($keywords->getText()));

            //transactional
            $image = (new Image())->setData($imageAI->getImages())
                ->setKeywords($keywords->getText())
                ->setContext($context)
                ->setCustomer($this->userRepository->findOrThrow($message->getUserId()));
            $this->imageRepository->save($image, false);

            $this->accountService->withdraw(
                $this->AIService->findCost(
                    TypeDataEnum::IMAGE,
                    $this->countImage
                ),
                $message->getUserId(),
                true
            );

            $this->logger->info('Image finished content message',
                [
                    'source' => $message->getSourceName(),
                    'title' => $message->getTitle()
                ]
            );

            $this->cache->invalidateTags([APIEnum::CACHE_TAG_USER->value . $message->getUserId()]);
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage(), (array) $ex);
            throw $ex;
        }
    }
}
