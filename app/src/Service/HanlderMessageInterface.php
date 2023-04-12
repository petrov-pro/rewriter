<?php
namespace App\Service;

use App\MessageHandler\Message\ContextInterface;

interface HanlderMessageInterface
{

    public function handle(ContextInterface $message): void;
}
