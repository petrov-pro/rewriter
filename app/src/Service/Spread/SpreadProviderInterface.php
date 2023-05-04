<?php
namespace App\Service\Spread;

use App\Entity\Context;

interface SpreadProviderInterface
{

    public function isSupport(string $providerType): bool;

    public function spread(array $params, Context $context): void;
}
