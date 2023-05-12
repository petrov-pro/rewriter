<?php
namespace App\Tests\Integration;

use App\Entity\Site;
use App\MessageHandler\Message\ContextInterface;
use App\MessageHandler\RewriteHandler;
use App\Repository\SiteRepository;
use App\Service\AccountService;
use App\Service\AI\AIInterface;
use App\Service\AI\OpenAIService;
use App\Service\ContextService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class RewriteHandlerTest extends TestCase
{

    private RewriteHandler $handler;
    private ContextInterface $message;
    private AIInterface $aiService;
    private AccountService $accountService;
    private SiteRepository $siteRepository;
    private LoggerInterface $logger;
    private MessageBusInterface $bus;
    private TagAwareCacheInterface $cache;
    private ContextService $contextService;

    protected function setUp(): void
    {
        $this->aiService = $this->createPartialMock(OpenAIService::class, ['rewrite', 'keywords']);
        $this->accountService = $this->createMock(AccountService::class);
        $this->siteRepository = $this->createMock(SiteRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->bus = $this->createMock(MessageBusInterface::class);
        $this->cache = $this->createMock(TagAwareCacheInterface::class);
        $this->contextService = $this->createMock(ContextService::class);

        $this->handler = new RewriteHandler(
            $this->contextService,
            $this->aiService,
            $this->accountService,
            $this->siteRepository,
            $this->logger,
            $this->bus,
            $this->cache,
            0
        );

        $this->message = $this->createMock(ContextInterface::class);
        $this->message->method('getSourceName')->willReturn('example.com');
        $this->message->method('getTitle')->willReturn('Lorem ipsum');
        $this->message->method('getLang')->willReturn('en');
        $this->message->method('getUserId')->willReturn(123);
        $this->message->method('getId')->willReturn(456);
        $this->message->method('getText')->willReturn('Lorem ipsum dolor sit amet');
        $this->message->method('getDescription')->willReturn('consectetur adipiscing elit');
        $this->message->method('getSiteId')->willReturn(789);
    }

    public function testHandleWithDuplicateTranslate()
    {
        $this->contextService->method('isDuplicateTranslate')->willReturn(true);

        $this->bus->expects($this->once())
            ->method('dispatch')
            ->withConsecutive(
                [$this->isInstanceOf(ContextInterface::class), $this->isType('array')]
            )
            ->willReturn(new Envelope(new stdClass()));

        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                ['Rewriter get content message', [
                        'source' => 'example.com',
                        'title' => 'Lorem ipsum',
                        'lang' => 'en'
                    ]],
                ['Rewriter skip duplicate', [
                        'message_id' => 456,
                        'user_id' => 123,
                        'lang' => 'en',
                    ]]
        );

        $this->handler->handle($this->message);
    }

    public function testHandleWithNotEnoughBalance()
    {
        $this->accountService->method('isEnoughBalance')->willReturn(false);

        $this->logger->expects($this->exactly(1))
            ->method('info')
            ->withConsecutive(
                ['Rewriter get content message', [
                        'source' => 'example.com',
                        'title' => 'Lorem ipsum',
                        'lang' => 'en'
                    ]]
        );

        $this->logger->expects($this->once())->method('warning')->with(
            'Rewriter skip content message because balance not enough',
            [
                'customer_id' => 123,
                'source' => 'example.com',
                'title' => 'Lorem ipsum',
                'lang' => 'en',
            ]
        );

        $this->handler->handle($this->message);
    }

    public function testSuccess()
    {
        $this->contextService->method('isDuplicateTranslate')->willReturn(false);
        $this->accountService->method('isEnoughBalance')->willReturn(true);
        $this->siteRepository->method('find')->willReturn((new Site())->setHtmlTag(''));

        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                ['Rewriter get content message', [
                        'source' => 'example.com',
                        'title' => 'Lorem ipsum',
                        'lang' => 'en'
                    ]]
        );

        $this->handler->handle($this->message);
    }
}
