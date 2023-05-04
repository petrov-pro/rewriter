<?php
namespace App\Service\Spread\DTO;

use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class BaseDTO
{

    #[Assert\Url]
    #[Assert\NotBlank]
    #[SerializedName('api_url')]
    protected string $apiUrl;
    protected string $password;
    protected string $login;
    protected string $token;
    protected string $site;

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setApiUrl(string $apiUrl)
    {
        $this->apiUrl = $apiUrl;
        return $this;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
        return $this;
    }

    public function setLogin(string $login)
    {
        $this->login = $login;
        return $this;
    }

    public function setToken(string $token)
    {
        $this->token = $token;
        return $this;
    }

    public static function toArray(): array
    {
        $reflection = new ReflectionClass(self::class);
        return $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
    }

    public function getSite(): string
    {
        return $this->site;
    }

    public function setSite(string $site)
    {
        $this->site = $site;
        return $this;
    }
}
