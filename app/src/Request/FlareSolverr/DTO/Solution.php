<?php
namespace App\Request\FlareSolverr\DTO;

class Solution
{

    private string $url;
    private int $status;
    private ?Headers $headers;
    private string $response;

    /** @var Cookies[] */
    private array $cookies;
    private string $userAgent;

    /**
     * @param Cookies[] $cookies
     */
    public function __construct(
        string $url,
        int $status,
        string $response,
        string $userAgent,
        Headers $headers = null,
        array $cookies = []
    )
    {
        $this->url = $url;
        $this->status = $status;
        $this->headers = $headers;
        $this->response = $response;
        $this->cookies = $cookies;
        $this->userAgent = $userAgent;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getHeaders(): Headers
    {
        return $this->headers;
    }

    public function getResponse(): string
    {
        return $this->response;
    }

    /**
     * @return Cookies[]
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }
}
