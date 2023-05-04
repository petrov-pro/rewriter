<?php
namespace App\Tests\Integration;

use App\Entity\Image;
use App\Entity\Site;
use App\Entity\User;
use App\MessageHandler\ImageHandler;
use App\MessageHandler\Message\ContextInterface;
use App\Repository\ImageRepository;
use App\Repository\SiteRepository;
use App\Repository\UserRepository;
use App\Service\AccountService;
use App\Service\AI\AIInterface;
use App\Service\AI\DTO\ImageInterface;
use App\Service\AI\DTO\TextInterface;
use App\Service\ContextService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class ImageHandlerTest extends TestCase
{

    private ImageRepository $imageRepository;
    private UserRepository $userRepository;
    private ContextService $contextService;
    private SiteRepository $siteRepository;
    private TagAwareCacheInterface $cache;
    private AccountService $accountService;
    private AIInterface $aiService;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->imageRepository = $this->createMock(ImageRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->contextService = $this->createMock(ContextService::class);
        $this->siteRepository = $this->createMock(SiteRepository::class);
        $this->cache = $this->createMock(TagAwareCacheInterface::class);
        $this->accountService = $this->createMock(AccountService::class);
        $this->aiService = $this->createMock(AIInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testHandle(): void
    {
        $message = $this->createMock(ContextInterface::class);
        $message->method('getSourceName')->willReturn('test_source');
        $message->method('getTitle')->willReturn('test_title');
        $message->method('getLang')->willReturn('en');
        $message->method('getSiteId')->willReturn(1);
        $message->method('getUserId')->willReturn(1);
        $message->method('getId')->willReturn(1);

        $keywordsMock = $this->createMock(TextInterface::class);
        $keywordsMock->method('getText')->willReturn('Crypto, bitcoin, money');
        $keywordsMock->method('getToken')->willReturn(1);

        $imageMock = $this->createMock(ImageInterface::class);
        $imageMock->method('getImages')->willReturn(['url_image', 'url_image1']);
        $imageMock->method('getCost')->willReturn(1);

        $handler = new ImageHandler(
            $this->aiService,
            $this->logger,
            $this->imageRepository,
            $this->userRepository,
            $this->contextService,
            $this->siteRepository,
            $this->cache,
            $this->accountService,
            true,
            1
        );

        $this->siteRepository->method('find')->willReturn(
            (new Site())->setIsImage(true)
        );
        $this->userRepository->method('findOrThrow')->willReturn(new User());
        $this->aiService->method('keywords')->willReturn($keywordsMock);
        $this->aiService->method('createImage')->willReturn($imageMock);
        $this->aiService->method('findSupposedCost')->willReturn(1);
        $this->aiService->method('findCost')->willReturn(1);
        $this->accountService->method('isEnoughBalance')->willReturn(true);
        $this->accountService->expects($this->exactly(2))->method('withdraw');
        $this->imageRepository->expects($this->once())->method('save')->willReturnCallback(function ($image, $flush) {
            $this->assertInstanceOf(Image::class, $image);
        });
        $this->logger->expects($this->exactly(2))->method('info');

        $handler->handle($message);
    }
}
