<?php
namespace App\Request\FlareSolverr\DTO;

class SiteDTO
{

    private ?Solution $solution;
    private string $status;
    private string $message;
    private int $startTimestamp;
    private int $endTimestamp;
    private string $version;

    public function __construct(
        string $status,
        string $message,
        int $startTimestamp,
        int $endTimestamp,
        string $version,
        Solution $solution = null
    )
    {
        $this->solution = $solution;
        $this->status = $status;
        $this->message = $message;
        $this->startTimestamp = $startTimestamp;
        $this->endTimestamp = $endTimestamp;
        $this->version = $version;
    }

    public function getSolution(): Solution
    {
        return $this->solution;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getStartTimestamp(): int
    {
        return $this->startTimestamp;
    }

    public function getEndTimestamp(): int
    {
        return $this->endTimestamp;
    }

    public function getVersion(): string
    {
        return $this->version;
    }
}
