<?php
namespace App\MessageHandler;

use App\MessageHandler\Message\ContextInterface;

interface HanlderMessageInterface
{

    public function handle(ContextInterface $message): void;
}
