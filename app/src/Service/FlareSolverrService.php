<?php
namespace App\Service;

use App\Request\FlareSolverr\FlareSolverrRequest;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ClientException;

class FlareSolverrService
{

    private const STATUS = 'ok';

    public function __construct(
        private FlareSolverrRequest $request,
        private LoggerInterface $logger
    )
    {
        
    }

    public function getData(string $url): string
    {
        try {
            $siteData = $this->request->get($url);

            if ($siteData->getStatus() !== self::STATUS) {
                throw new InvalidArgumentException('Status response not ok');
            }

            return $siteData
                    ->getSolution()
                    ->getResponse();
        } catch (ClientException $exc) {
            $this->logger->error($exc->getMessage(),
                [
                    'class' => __CLASS__,
                    'response' => $exc
                ]
            );
            throw $exc;
        } catch (Exception $exc) {
            $this->logger->error($exc->getMessage(), (array) $exc);
            throw $exc;
        }
    }
}
