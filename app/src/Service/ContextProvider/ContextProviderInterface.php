<?php
namespace App\Service\ContextProvider;

use App\MessageHandler\Message\ContextInterface;

interface ContextProviderInterface
{

    /**
     * @return ContextInterface[]
     */
    public function getContexts(): array;

    public function getProviderName(): string;
    
}
