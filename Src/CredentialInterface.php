<?php
declare(strict_types=1);

namespace Lostfocus\Tripit;

interface CredentialInterface
{
    public function authorize(\CurlHandle $curl, string $httpMethod, string $realm, string $baseUrl, ?array $args = null): \CurlHandle;
}