<?php
namespace App\Request\FlareSolverr\DTO;

class Cookies
{

    private ?string $name;
    private ?string $value;
    private ?string $domain;
    private ?string $path;
    private ?float $expires;
    private ?int $size;
    private ?bool $httpOnly;
    private ?bool $secure;
    private ?bool $session;
    private ?string $sameSite;

    public function __construct(
        ?string $name,
        ?string $value,
        ?string $domain,
        ?string $path,
        ?float $expires,
        ?int $size,
        ?bool $httpOnly,
        ?bool $secure,
        ?bool $session,
        ?string $sameSite
    )
    {
        $this->name = $name;
        $this->value = $value;
        $this->domain = $domain;
        $this->path = $path;
        $this->expires = $expires;
        $this->size = $size;
        $this->httpOnly = $httpOnly;
        $this->secure = $secure;
        $this->session = $session;
        $this->sameSite = $sameSite;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getExpires(): ?float
    {
        return $this->expires;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getHttpOnly(): ?bool
    {
        return $this->httpOnly;
    }

    public function getSecure(): ?bool
    {
        return $this->secure;
    }

    public function getSession(): ?bool
    {
        return $this->session;
    }

    public function getSameSite(): ?string
    {
        return $this->sameSite;
    }
}
