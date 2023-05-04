<?php
namespace App\Tests\Integration;

use App\Entity\Site;
use App\Entity\User;
use App\MessageHandler\Message\ContextInterface;
use App\MessageHandler\OrderHandler;
use App\MessageHandler\RewriteHandler;
use App\Repository\UserRepository;
use App\Request\Cryptonews\DTO\NewsDTO;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

class OrderHandlerTest extends TestCase
{

    private $logger;
    private $userRepository;
    private $bus;
    private $availableLangs;
    private $needCreateImage;
    private $handler;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->availableLangs = ['en', 'fr'];
        $this->needCreateImage = true;

        $this->handler = new OrderHandler(
            $this->logger,
            $this->userRepository,
            $this->bus,
            $this->availableLangs,
            $this->needCreateImage
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

        $user->addSite($site);

        $this->userRepository->expects($this->once())
            ->method('findAllActive')
            ->with($category)
            ->willReturn([$user]);

        $context = new NewsDTO();
        $context->setCategory($category)
            ->setTitle($title)
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
