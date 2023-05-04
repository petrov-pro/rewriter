<?php
namespace App\Tests\Integration;

use App\Entity\Context;
use App\MessageHandler\Message\ContextInterface;
use App\MessageHandler\ParserHandler;
use App\Service\ContextService;
use App\Service\Parser\ParserFactory;
use App\Service\Parser\SiteParserInterface;
use App\Service\Thief\ThiefInterface;
use App\Util\NormalizeText;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class ParserHandlerTest extends TestCase
{

    private $logger;
    private $bus;
    private $parserFactory;
    private $contextService;
    private $capture;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->parserFactory = $this->createMock(ParserFactory::class);
        $this->contextService = $this->createMock(ContextService::class);
        $this->capture = $this->createMock(ThiefInterface::class);
    }

    public function testHandle()
    {
        $sourceUrl = 'https://example.com';
        $sourceName = 'example';
        $title = 'Example Title';
        $fullText = 'Example Text';
        $contextId = 1;

        $message = $this->createMock(ContextInterface::class);
        $message->expects($this->once())
            ->method('getId')
            ->willReturn($contextId);
        $message->expects($this->exactly(3))
            ->method('getSourceName')
            ->willReturn($sourceName);
        $message->expects($this->once())
            ->method('getSourceUrl')
            ->willReturn($sourceUrl);
        $message->expects($this->exactly(2))
            ->method('getTitle')
            ->willReturn($title);
        $message->expects($this->once())
            ->method('getText')
            ->willReturn($fullText);

        $this->capture->expects($this->once())
            ->method('getData')
            ->with($sourceUrl)
            ->willReturn('Example Source Data');

        $siteParser = $this->createMock(SiteParserInterface::class);
        $siteParser->expects($this->once())
            ->method('parser')
            ->with('Example Source Data')
            ->willReturn($fullText);
        $this->parserFactory->expects($this->once())
            ->method('create')
            ->with($sourceName)
            ->willReturn($siteParser);

        $this->contextService->expects($this->once())
            ->method('updateStatusText')
            ->with($contextId, Context::STATUS_FINISH, NormalizeText::handle($fullText));

        $this->bus->expects($this->once())
            ->method('dispatch')
            ->withConsecutive(
                [$this->isInstanceOf(ContextInterface::class), $this->isType('array')],
                [$this->isInstanceOf(ContextInterface::class), $this->isType('array')],
            )
            ->willReturn(new Envelope(new stdClass()));

        $parserHandler = new ParserHandler(
            $this->logger,
            $this->bus,
            $this->parserFactory,
            $this->contextService,
            $this->capture
        );

        $parserHandler->handle($message);
    }
}
