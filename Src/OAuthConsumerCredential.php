<?php
declare(strict_types=1);

namespace Lostfocus\Tripit;

class OAuthConsumerCredential implements CredentialInterface
{
    public const OAUTH_SIGNATURE_METHOD = 'HMAC-SHA1';
    public const OAUTH_VERSION = '1.0';

    private string $oauthConsumerKey;
    private string $oauthConsumerSecret;
    private string $oauthToken;
    private string $oauthTokenSecret;
    private string $oauthRequestorId;

    public function __construct(string $oauthConsumerKey, string $oauthConsumerSecret, string $oauthTokenOrRequestorId = '', string $oauthTokenSecret = '')
    {
        $this->oauthConsumerKey = $oauthConsumerKey;
        $this->oauthConsumerSecret = $oauthConsumerSecret;

        $this->oauthToken = $this->oauthTokenSecret = $this->oauthRequestorId = '';
        if ($oauthTokenOrRequestorId && $oauthTokenSecret) {
            $this->oauthToken = $oauthTokenOrRequestorId;
            $this->oauthTokenSecret = $oauthTokenSecret;
        } elseif ($oauthTokenOrRequestorId) {
            $this->oauthRequestorId = $oauthTokenOrRequestorId;
        }
    }

    public function getOAuthConsumerKey(): string
    {
        return $this->oauthConsumerKey;
    }

    public function getOAuthConsumerSecret(): string
    {
        return $this->oauthConsumerSecret;
    }

    public function getOAuthToken(): string
    {
        return $this->oauthToken;
    }

    public function getOAuthTokenSecret(): string
    {
        return $this->oauthTokenSecret;
    }

    public function getOAuthRequestorId(): string
    {
        return $this->oauthRequestorId;
    }

    public function authorize(\CurlHandle $curl, string $httpMethod, string $realm, string $baseUrl, ?array $args = null): \CurlHandle
    {
        $authorization_header = $this->generateAuthorizationHeader($httpMethod, $realm, $baseUrl, $args);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: '.$authorization_header));

        return $curl;
    }

    private function generateAuthorizationHeader(string $httpMethod, string $realm, string $baseUrl, ?array $args = null): string
    {
        $authorization_header = 'OAuth realm="'.$realm.'",';

        $params = [];
        foreach ($this->generateOauthParameters($httpMethod, $baseUrl, $args) as $k => $v) {
            if (str_starts_with($k, 'oauth') || str_starts_with($k, 'xoauth')) {
                $params[] = OAuthUtil::urlencodeRFC3986($k).'="'.OAuthUtil::urlencodeRFC3986((string)$v).'"';
            }
        }
        $authorization_header .= implode(',', $params);

        return $authorization_header;
    }

    private function generateOauthParameters(string $httpMethod, string $baseUrl, ?array $args = null): array
    {
        $httpMethod = strtoupper($httpMethod);

        $parameters = array(
            'oauth_consumer_key' => $this->oauthConsumerKey,
            'oauth_nonce' => OAuthUtil::generate_nonce(),
            'oauth_timestamp' => OAuthUtil::generate_timestamp(),
            'oauth_signature_method' => self::OAUTH_SIGNATURE_METHOD,
            'oauth_version' => self::OAUTH_VERSION,
        );

        if ($this->oauthToken !== '') {
            $parameters['oauth_token'] = $this->oauthToken;
        }

        if ($this->oauthRequestorId !== '') {
            $parameters['xoauth_requestor_id'] = $this->oauthRequestorId;
        }

        $parametersForBaseString = $parameters;
        if ($args) {
            $parametersForBaseString = array_merge($parameters, $args);
        }

        $parameters['oauth_signature'] = $this->generateSignature($httpMethod, $baseUrl, $parametersForBaseString);

        return $parameters;
    }

    private function generateSignature(string $httpMethod, string $baseUrl, array $params): string
    {
        $normalized_parameters = OAuthUtil:: urlencodeRFC3986($this->getSignableParameters($params));

        $normalized_http_url = OAuthUtil:: urlencodeRFC3986($baseUrl);

        $base_string = $httpMethod.'&'.$normalized_http_url;
        if ($normalized_parameters) {
            $base_string .= '&'.$normalized_parameters;
        }

        $key_parts = array($this->oauthConsumerSecret, $this->oauthTokenSecret);

        $key_parts = array_map(array(
            OAuthUtil::class,
            'urlencodeRFC3986',
        ), $key_parts);
        $key = implode('&', $key_parts);

        return base64_encode(hash_hmac('sha1', $base_string, $key, true));
    }

    private function getSignableParameters(array $params): string
    {
        // Remove oauth_signature if present
        if (isset ($params['oauth_signature'])) {
            unset ($params['oauth_signature']);
        }

        // Urlencode both keys and values
        $keys = array_map(array(
            OAuthUtil::class,
            'urlencodeRFC3986',
        ), array_keys($params));
        $values = array_map([
            OAuthUtil::class,
            'urlencodeRFC3986',
        ], array_values($params));
        $params = array_combine($keys, $values);

        // Sort by keys (natsort)
        uksort($params, 'strnatcmp');

        // Generate key=value pairs
        $pairs = array();
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                // If the value is an array, it's because there are multiple
                // with the same key, sort them, then add all the pairs
                natsort($value);
                foreach ($value as $v2) {
                    $pairs[] = $key.'='.$v2;
                }
            } else {
                $pairs[] = $key.'='.$value;
            }
        }

        // Return the pairs, concated with &
        return implode('&', $pairs);
    }

    public function validateSignature(string $url): bool
    {
        [$baseUrl, $query] = explode('?', $url, 2);
        $params = [];
        parse_str($query, $params);
        $signature = $params['oauth_signature'];

        return ($signature === $this->generateSignature('GET', $baseUrl, $params));
    }

    /**
     * @throws \JsonException
     */
    public function getSessionParameters(string $redirectUrl, string $action): bool|string
    {
        $parameters = $this->generateOauthParameters('GET', $action, ['redirect_url' => $redirectUrl]);
        $parameters['redirect_url'] = $redirectUrl;
        $parameters['action'] = $action;

        return json_encode($parameters, JSON_THROW_ON_ERROR);
    }
}