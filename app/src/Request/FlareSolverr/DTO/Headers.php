<?php
namespace App\Request\FlareSolverr\DTO;

class Headers
{

    private ?string $status;
    private ?string $date;
    private ?string $expires;
    private ?string $cacheControl;
    private ?string $contentType;
    private ?string $strictTransportSecurity;
    private ?string $p3p;
    private ?string $contentEncoding;
    private ?string $server;
    private ?string $contentLength;
    private ?string $xXssProtection;
    private ?string $xFrameOptions;
    private ?string $setCookie;

    public function __construct(
        ?string $status,
        ?string $date,
        ?string $expires,
        ?string $cacheControl,
        ?string $contentType,
        ?string $strictTransportSecurity,
        ?string $p3p,
        ?string $contentEncoding,
        ?string $server,
        ?string $contentLength,
        ?string $xXssProtection,
        ?string $xFrameOptions,
        ?string $setCookie
    )
    {
        $this->status = $status;
        $this->date = $date;
        $this->expires = $expires;
        $this->cacheControl = $cacheControl;
        $this->contentType = $contentType;
        $this->strictTransportSecurity = $strictTransportSecurity;
        $this->p3p = $p3p;
        $this->contentEncoding = $contentEncoding;
        $this->server = $server;
        $this->contentLength = $contentLength;
        $this->xXssProtection = $xXssProtection;
        $this->xFrameOptions = $xFrameOptions;
        $this->setCookie = $setCookie;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function getExpires(): ?string
    {
        return $this->expires;
    }

    public function getCacheControl(): ?string
    {
        return $this->cacheControl;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function getStrictTransportSecurity(): ?string
    {
        return $this->strictTransportSecurity;
    }

    public function getP3p(): ?string
    {
        return $this->p3p;
    }

    public function getContentEncoding(): ?string
    {
        return $this->contentEncoding;
    }

    public function getServer(): ?string
    {
        return $this->server;
    }

    public function getContentLength(): ?string
    {
        return $this->contentLength;
    }

    public function getXXssProtection(): ?string
    {
        return $this->xXssProtection;
    }

    public function getXFrameOptions(): ?string
    {
        return $this->xFrameOptions;
    }

    public function getSetCookie(): ?string
    {
        return $this->setCookie;
    }
}
