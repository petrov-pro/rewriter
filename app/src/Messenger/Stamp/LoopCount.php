<?php
namespace App\Messenger\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

class LoopCount implements StampInterface
{

    public function __construct(private int $maxRepeat, private int $count = 0)
    {
        
    }

    public function isMaxCount(): bool
    {
        return $this->count >= $this->maxRepeat;
    }

    public function increaseCount(): void
    {
        $this->count++;
    }

}
