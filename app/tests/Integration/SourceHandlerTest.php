<?php
namespace App\Tests\Integration;

use App\Entity\Context;
use App\MessageHandler\SourceHandler;
use App\MessageHandler\Message\ContextInterface;
use App\Service\ContextProvider\ContextProviderInterface;
use App\Service\ContextService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class SourceHandlerTest extends TestCase
{

    public function testHandle(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $bus = $this->createMock(MessageBusInterface::class);
        $contextProviders = [
            $this->createMock(ContextProviderInterface::class),
        ];
        $contextService = $this->createMock(ContextService::class);
        $handler = new SourceHandler($logger, $bus, $contextProviders, $contextService);

        $expectedTitle = 'Title';
        $expectedSourceName = 'SourceName';
        $expectedProviderName = 'ProviderName';
        $expectedId = 123;

        $contexts = [
            $this->createMock(ContextInterface::class),
        ];

        $contextProviders[0]
            ->method('getProviderName')
            ->willReturn($expectedProviderName);

        $contextProviders[0]
            ->expects($this->once())
            ->method('getContexts')
            ->willReturn($contexts);

        $contexts[0]
            ->expects($this->exactly(1))
            ->method('getTitle')
            ->willReturn($expectedTitle);

        $contexts[0]
            ->expects($this->exactly(2))
            ->method('getSourceName')
            ->willReturn($expectedSourceName);

        $contextService
            ->expects($this->exactly(1))
            ->method('isDuplicate')
            ->with($expectedTitle, $expectedSourceName)
            ->willReturn(false);

        $contexts[0]
            ->expects($this->once())
            ->method('setProvider')
            ->with($expectedProviderName);

        $contextService
            ->expects($this->once())
            ->method('create')
            ->with($contexts[0])
            ->willReturn((new Context())->setId(123));

        $contexts[0]
            ->expects($this->once())
            ->method('setId')
            ->with($expectedId);

        $bus
            ->expects($this->once())
            ->method('dispatch')
            ->willReturn(new Envelope(new stdClass()));

        $handler->handle();
    }
}
