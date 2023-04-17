<?php
namespace App\Controller\DTO;

class ErrorDTO
{

    private string $status = '';
    private string $message = '';

    public function __construct(string $status, string $message)
    {
        $this->status = $status;
        $this->message = $message;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function __toString()
    {
        return \json_encode(get_object_vars($this));
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
