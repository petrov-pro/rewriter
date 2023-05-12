<?php
namespace App\Messenger\Trait;

use App\Messenger\Stamp\LoopCount;

trait LoopTrait
{

    private LoopCount $loopCount;

    public function setLoopCount(LoopCount $loopCount): self
    {
        $this->loopCount = $loopCount;

        return $this;
    }
}
