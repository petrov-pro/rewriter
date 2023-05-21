<?php
namespace App\Tests\Integration;

use App\Entity\Context;
use App\Entity\Site;
use App\Entity\Translate;
use App\MessageHandler\Message\ContextInterface;
use App\MessageHandler\SpreadHandler;
use App\Repository\ContextRepository;
use App\Repository\SiteRepository;
use App\Service\Spread\SpreadProviderFactory;
use App\Service\Spread\WordPress\WordPressProvider;
use App\Service\Spread\WordPressCom\WordPressComProvider;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class SpreadHandlerWordPressTest extends KernelTestCase
{

    private $loggerMock;
    private $siteRepositoryMock;
    private SpreadProviderFactory $spreadProviderFactory;
    private ContextRepository $contextRepositoryMock;
    private WordPressProvider $wordPressProvider;

    protected function setUp(): void
    {
        self::bootKernel([
            'environment' => 'test',
            'debug' => false,
        ]);
        self::getContainer()->set(MockHttpClient::class, new MockHttpClient(
                function ($method, $url, $options) {
                    if ($method === \Symfony\Component\HttpFoundation\Request::METHOD_POST) {
                        return new MockResponse(\json_encode(
                                [
                                    'title' => 'created'
                                ]
                            ), ['http_code' => 200]);
                    } else {
                        return new MockResponse('', ['http_code' => 200]);
                    }
                }
        ));

        $this->wordPressProvider = self::getContainer()->get(WordPressProvider::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->siteRepositoryMock = $this->createMock(SiteRepository::class);
        $this->spreadProviderFactory = self::getContainer()->get(SpreadProviderFactory::class);
        $this->contextRepositoryMock = $this->createMock(ContextRepository::class);
    }

    public function testHandleDoesNotSendSpreadIfSiteIsNotSent()
    {
        $messageMock = $this->createMock(ContextInterface::class);
        $messageMock->expects($this->once())
            ->method('getSourceName')
            ->willReturn('Mock Source');

        $messageMock->expects($this->once())
            ->method('getTitle')
            ->willReturn('Mock Title');

        $messageMock->expects($this->once())
            ->method('getSiteId')
            ->willReturn(1);

        $siteMock = $this->createMock(Site::class);
        $siteMock->expects($this->once())
            ->method('isSend')
            ->willReturn(false);
        $siteMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->siteRepositoryMock->expects($this->once())
            ->method('find')
            ->willReturn($siteMock);

        $this->loggerMock->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                ['Spread get content message', ['source' => 'Mock Source', 'title' => 'Mock Title']],
                ['Site has disable option is_sent: 1']
        );

        $handler = new SpreadHandler($this->loggerMock, $this->siteRepositoryMock, $this->spreadProviderFactory, $this->contextRepositoryMock);
        $handler->handle($messageMock);
    }

    public function testWordPressSpread()
    {
        $messageMock = $this->createMock(ContextInterface::class);
        $messageMock->expects($this->once())
            ->method('getSourceName')
            ->willReturn('Mock Source');

        $messageMock->expects($this->once())
            ->method('getTitle')
            ->willReturn('Mock Title');

        $messageMock->expects($this->once())
            ->method('getSiteId')
            ->willReturn(1);

        $messageMock->expects($this->once())
            ->method('getLang')
            ->willReturn("en");

        $siteMock = (new Site())
            ->setType(WordPressProvider::TYPE)
            ->setId(1)
            ->setSetting([
                'login' => 'test',
                'password' => 'test',
                'api_url' => 'https://test.com/',
                'post_create' => [
                    'status' => 'publish',
                    'categories' => [
                        'crypto'
                    ]
                ]
            ])
            ->setIsSend(true);

        $this->siteRepositoryMock->expects($this->once())
            ->method('find')
            ->willReturn($siteMock);

        $contextMock = (new Context())->setId(1)
            ->setCategory(['crypto'])
            ->setDescription('Text description')
            ->setTitle('Title text')
            ->setText('Text')
            ->addTranslate(
            (new Translate())->setTitle('Rewrite text')
            ->setDescription('Rewriter description')
            ->setText('Rewriter text')
        );

        $this->contextRepositoryMock->expects($this->once())
            ->method('findByIdLang')
            ->willReturn($contextMock);

        $this->loggerMock->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                ['Spread get content message', ['source' => 'Mock Source', 'title' => 'Mock Title']],
                ['Spread finished.']
        );

        $handler = new SpreadHandler($this->loggerMock, $this->siteRepositoryMock, $this->spreadProviderFactory, $this->contextRepositoryMock);
        $handler->handle($messageMock);
    }

    public function testWordPressComSpread()
    {
        $messageMock = $this->createMock(ContextInterface::class);
        $messageMock->expects($this->once())
            ->method('getSourceName')
            ->willReturn('Mock Source');

        $messageMock->expects($this->once())
            ->method('getTitle')
            ->willReturn('Mock Title');

        $messageMock->expects($this->once())
            ->method('getSiteId')
            ->willReturn(1);

        $siteMock = (new Site())
            ->setType(WordPressComProvider::TYPE)
            ->setId(1)
            ->setSetting([
                'site' => 'test',
                'token' => 'test',
                'api_url' => 'https://test.com/',
                'post_create' => [
                    'status' => 'publish',
                    'categories' => [
                        'crypto'
                    ]
                ]
            ])
            ->setIsSend(true);

        $this->siteRepositoryMock->expects($this->once())
            ->method('find')
            ->willReturn($siteMock);

        $contextMock = (new Context())->setId(1)
            ->setCategory(['crypto'])
            ->setDescription('Text description')
            ->setTitle('Title text')
            ->setText('Text')
            ->addTranslate(
            (new Translate())->setTitle('Rewrite text')
            ->setDescription('Rewriter description')
            ->setText('Rewriter text')
        );

        $this->contextRepositoryMock->expects($this->once())
            ->method('findByIdLang')
            ->willReturn($contextMock);

        $this->loggerMock->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                ['Spread get content message', ['source' => 'Mock Source', 'title' => 'Mock Title']],
                ['Spread finished.']
        );

        $handler = new SpreadHandler($this->loggerMock, $this->siteRepositoryMock, $this->spreadProviderFactory, $this->contextRepositoryMock);
        $handler->handle($messageMock);
    }
}
