<?php
declare(strict_types=1);

namespace Lostfocus\Tripit;

class OAuthUtil
{
    public static function urlencodeRFC3986(string $string): string
    {
        /** @noinspection CascadeStringReplacementInspection */
        return str_replace('+', ' ', str_replace('%7E', '~', rawurlencode($string)));
    }

    public static function generate_nonce(): string
    {
        return md5(microtime().mt_rand()); // md5s look nicer than numbers
    }

    public static function generate_timestamp(): int
    {
        return time();
    }
}