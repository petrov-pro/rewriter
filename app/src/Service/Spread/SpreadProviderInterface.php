<?php
namespace App\Service\Spread;

use App\Entity\Context;
use App\Entity\Site;

interface SpreadProviderInterface
{

    public function isSupport(string $providerType): bool;

    public function spread(Context $context, Site $site): void;
}
