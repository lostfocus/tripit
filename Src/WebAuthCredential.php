<?php

declare(strict_types=1);

namespace Lostfocus\Tripit;

class WebAuthCredential implements CredentialInterface
{
    public function __construct(
        private string $username,
        private string $password
    ) {

    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param  array<string, mixed>|null  $args
     */
    public function authorize(\CurlHandle $curl, string $httpMethod, string $realm, string $baseUrl, ?array $args = null): \CurlHandle
    {
        curl_setopt($curl, CURLOPT_USERPWD, $this->username.":".$this->password);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        return $curl;
    }
}
