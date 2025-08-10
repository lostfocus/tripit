<?php
declare(strict_types=1);

namespace Lostfocus\Tripit;

class OAuthUtil
{
    /**
     * @param  string|string[]  $value
     * @return string|string[]
     */
    public static function urlencodeRFC3986(string|array|int $value): string|array
    {
        if (is_array($value)) {
            $return = [];
            foreach ($value as $v) {
                $v = self::urlencodeRFC3986($v);
                assert(is_string($v));
                $return[] = $v;
            }

            return $return;
        }

        /** @noinspection CascadeStringReplacementInspection */
        return str_replace('+', ' ', str_replace('%7E', '~', rawurlencode((string)$value)));
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