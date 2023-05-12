<?php
namespace App\Messenger;

use App\Messenger\Stamp\LoopCount;

interface LoopMessageInterface
{

    public function setLoopCount(LoopCount $loopCount): self;
}
