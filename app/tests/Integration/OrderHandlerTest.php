<?php
namespace App\Tests\Integration;

use App\Entity\Site;
use App\Entity\User;
use App\MessageHandler\Message\ContextInterface;
use App\MessageHandler\OrderHandler;
use App\Repository\SiteRepository;
use App\Repository\TranslateRepository;
use App\Repository\UserRepository;
use App\Request\Cryptonews\DTO\NewsDTO;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class OrderHandlerTest extends TestCase
{

    private $logger;
    private $userRepository;
    private $bus;
    private $availableLangs;
    private $needCreateImage;
    private $handler;
    private $siteRepository;
    private $translateRepository;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->siteRepository = $this->createMock(SiteRepository::class);
        $this->translateRepository = $this->createMock(TranslateRepository::class);
        $this->availableLangs = ['en', 'fr'];
        $this->needCreateImage = true;

        $this->handler = new OrderHandler(
            $this->logger,
            $this->userRepository,
            $this->bus,
            $this->siteRepository,
            $this->translateRepository,
            $this->availableLangs,
            $this->needCreateImage,
            1,
            2
        );
    }

    public function testHandleSendsExpectedMessages()
    {
        $category = ['food'];
        $userId = 1;
        $siteId = 2;
        $title = 'Test order';
        $sourceName = 'test-source';
        $langs = $this->availableLangs;

        $user = new User();
        $user->setId($userId);

        $site = new Site();
        $site->setId($siteId);
        $site->setLang($langs);
        $site->setIsImage(true);

        $user->addSite($site);

        $this->userRepository->expects($this->once())
            ->method('findAllActive')
            ->with($category)
            ->willReturn([$user]);

        $context = new NewsDTO();
        $context->setCategory($category)
            ->setTitle($title)
            ->setId(2)
            ->setSourceName($sourceName);

        $this->bus->expects($this->exactly(3))
            ->method('dispatch')
            ->withConsecutive(
                [$this->isInstanceOf(ContextInterface::class), $this->isType('array')],
                [$this->isInstanceOf(ContextInterface::class), $this->isType('array')],
            )
            ->willReturn(new Envelope(new stdClass()));

        $this->handler->handle($context);
    }
}
