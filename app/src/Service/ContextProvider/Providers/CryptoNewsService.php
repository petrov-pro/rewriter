<?php
namespace App\Service\ContextProvider\Providers;

use App\MessageHandler\Message\ContextInterface;
use App\Request\Cryptonews\CryptonewsRequest;
use App\Service\ContextProvider\ContextProviderInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ClientException;

class CryptoNewsService implements ContextProviderInterface
{

    public function __construct(
        private LoggerInterface $logger,
        private CryptonewsRequest $request
    )
    {
        
    }

    /**
     * @return ContextInterface[]
     */
    public function getContexts(): array
    {
        try {
            $news = $this->request->getGeneralNews();
            $this->logger->info('Get news', [
                'news' => $news
            ]);

            return $news;
        } catch (ClientException $exc) {
            $this->logger->error($exc->getMessage(),
                [
                    'response' => $exc
                ]
            );
            throw $exc;
        } catch (Exception $exc) {
            $this->logger->error($exc->getMessage());
            throw $exc;
        }
    }

    public function getProviderName(): string
    {
        return self::class;
    }
}
