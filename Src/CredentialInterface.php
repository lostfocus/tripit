<?php
declare(strict_types=1);

namespace Lostfocus\Tripit;

interface CredentialInterface
{
    /**
     * @param  array<string, mixed>|null  $args
     */
    public function authorize(\CurlHandle $curl, string $httpMethod, string $realm, string $baseUrl, ?array $args = null): \CurlHandle;
}