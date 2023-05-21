<?php
namespace App\Service\Spread;

use Exception;

class SpreadProviderFactory
{

    /**
     * @param SpreadProviderInterface[] $spreadProviders
     */
    public function __construct(
        private iterable $spreadProviders
    )
    {
        
    }

    public function create(string $providerType): SpreadProviderInterface
    {
        foreach ($this->spreadProviders as $provider) {
            if ($provider->isSupport($providerType)) {
                return $provider;
            }
        }
        throw new Exception('Not found spread provider for site type: ' . $providerType);
    }
}
