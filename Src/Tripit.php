<?php

declare(strict_types=1);

namespace Lostfocus\Tripit;

class Tripit
{
    private ?int $httpCode = null;

    public function __construct(private CredentialInterface $credential, private string $apiUrl = 'https://api.tripit.com', private string $apiVersion = 'v1')
    {
    }

    /**
     * @param  int|string  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function get_trip(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, urlArgs: $filter);
    }

    /**
     * @param  string  $funcName
     * @param  array<string, string|int>|null  $urlArgs
     * @param  array<string, string|int|array<string|int, mixed>>|null  $postArgs
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     * @throws \Exception
     */
    private function parseCommand(string $funcName, ?array $urlArgs = null, ?array $postArgs = null): array|\SimpleXMLElement
    {
        $pieces = explode('_', $funcName, 2);
        $verb = $pieces[0];
        $entity = count($pieces) > 1 ? $pieces[1] : null;

        $response = $this->doRequest($verb, $entity, $urlArgs, $postArgs);
        if (!is_string($response)) {
            throw new \RuntimeException('Response is not a string');
        }
        $format = 'xml';
        if (isset($urlArgs) && array_key_exists('format', $urlArgs) && is_string($urlArgs['format'])) {
            $format = $urlArgs['format'];
        } elseif (isset($postArgs) && array_key_exists('format', $postArgs) && is_string($postArgs['format'])) {
            $format = $postArgs['format'];
        }
        if (strtolower($format) === 'json') {
            $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            assert(is_array($response));

            return $response;
        }

        return $this->xmlToPhp($response);
    }

    /**
     * @param  string  $verb
     * @param  string|null  $entity
     * @param  array<string, string|int>|null  $urlArgs
     * @param  array<string, string|int|array<string|int, mixed>>|null  $postArgs
     * @return bool|string
     */
    private function doRequest(string $verb, ?string $entity = null, ?array $urlArgs = null, ?array $postArgs = null): bool|string
    {
        if (in_array($verb, ['/oauth/request_token', '/oauth/access_token'])) {
            $baseUrl = $this->apiUrl.$verb;
        } elseif ($entity) {
            $baseUrl = implode('/', [$this->apiUrl, $this->apiVersion, $verb, $entity]);
        } else {
            $baseUrl = implode('/', [$this->apiUrl, $this->apiVersion, $verb]);
        }

        $args = null;
        if ($urlArgs) {
            $args = $urlArgs;
            $pairs = [];
            foreach ($urlArgs as $name => $value) {
                $pairs[] = urlencode((string)$name).'='.urlencode((string)$value);
            }
            $url = $baseUrl.'?'.implode('&', $pairs);
        } else {
            $url = $baseUrl;
        }

        $curl = curl_init($this->apiUrl);
        if ($curl === false) {
            throw new \RuntimeException('Curl init failed');
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // In case you're running this against a server w/o
        // properly signed certs
        // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        if ($postArgs) {
            $args = $postArgs;
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postArgs);
            $http_method = 'POST';
        } else {
            $http_method = 'GET';
        }

        $this->credential->authorize($curl, $http_method, $this->apiUrl, $baseUrl, $args);

        if (false === $response = curl_exec($curl)) {
            throw new \RuntimeException(curl_error($curl));
        }

        $info = curl_getinfo($curl);
        $this->httpCode = $info['http_code'];
        curl_close($curl);

        return $response;
    }

    /**
     * @throws \Exception
     */
    private function xmlToPhp(string $xml): \SimpleXMLElement
    {
        return new \SimpleXMLElement($xml);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function get_air(string|int $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, urlArgs: $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function get_lodging(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function get_car(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function get_rail(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function get_transport(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function get_cruise(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function get_restaurant(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function get_activity(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function get_note(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function get_map(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function get_directions(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, $filter);
    }

    /**
     * @param  array<string, string|int>|null  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function get_profile(?array $filter = null): \SimpleXMLElement|array
    {
        return $this->parseCommand(__FUNCTION__, urlArgs: $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function get_points_program(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function delete_trip(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function delete_air(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function delete_lodging(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function delete_car(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, urlArgs: $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function delete_rail(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, urlArgs: $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function delete_transport(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, urlArgs: $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function delete_cruise(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, urlArgs: $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function delete_restaurant(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, urlArgs: $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function delete_activity(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, urlArgs: $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function delete_note(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, urlArgs: $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function delete_map(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, urlArgs: $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function delete_directions(int|string $id, array $filter = []): \SimpleXMLElement|array
    {
        $filter['id'] = $id;

        return $this->parseCommand(__FUNCTION__, urlArgs: $filter);
    }

    /**
     * @param  string|int  $id
     * @param  array<string|int, string|int|array<string|int, mixed>>  $data
     * @param  string  $format
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function replace_trip(string|int $id, array $data, string $format = 'xml'): \SimpleXMLElement|array
    {
        return $this->parseCommand(__FUNCTION__, postArgs: ['id' => $id, 'format' => $format, $format => $data]);
    }

    /**
     * @param  string|int  $id
     * @param  array<string|int, string|int|array<string|int, mixed>>  $data
     * @param  string  $format
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function replace_air(string|int $id, array $data, string $format = 'xml'): \SimpleXMLElement|array
    {
        return $this->parseCommand(__FUNCTION__, null, ['id' => $id, 'format' => $format, $format => $data]);
    }

    /**
     * @param  string|int  $id
     * @param  array<string|int, string|int|array<string|int, mixed>>  $data
     * @param  string  $format
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function replace_lodging(string|int $id, array $data, string $format = 'xml'): \SimpleXMLElement|array
    {
        return $this->parseCommand(__FUNCTION__, null, ['id' => $id, 'format' => $format, $format => $data]);
    }

    /**
     * @param  string|int  $id
     * @param  array<string|int, string|int|array<string|int, mixed>>  $data
     * @param  string  $format
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function replace_car(string|int $id, array $data, string $format = 'xml'): \SimpleXMLElement|array
    {
        return $this->parseCommand(__FUNCTION__, null, ['id' => $id, 'format' => $format, $format => $data]);
    }

    /**
     * @param  string|int  $id
     * @param  array<string|int, string|int|array<string|int, mixed>>  $data
     * @param  string  $format
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function replace_rail(string|int $id, array $data, string $format = 'xml'): \SimpleXMLElement|array
    {
        return $this->parseCommand(__FUNCTION__, null, ['id' => $id, 'format' => $format, $format => $data]);
    }

    /**
     * @param  string|int  $id
     * @param  array<string|int, string|int|array<string|int, mixed>>  $data
     * @param  string  $format
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function replace_transport(string|int $id, array $data, string $format = 'xml'): \SimpleXMLElement|array
    {
        return $this->parseCommand(__FUNCTION__, null, ['id' => $id, 'format' => $format, $format => $data]);
    }

    /**
     * @param  string|int  $id
     * @param  array<string|int, string|int|array<string|int, mixed>>  $data
     * @param  string  $format
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function replace_cruise(string|int $id, array $data, string $format = 'xml'): \SimpleXMLElement|array
    {
        return $this->parseCommand(__FUNCTION__, null, ['id' => $id, 'format' => $format, $format => $data]);
    }

    /**
     * @param  string|int  $id
     * @param  array<string|int, string|int|array<string|int, mixed>>  $data
     * @param  string  $format
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function replace_restaurant(string|int $id, array $data, string $format = 'xml'): \SimpleXMLElement|array
    {
        return $this->parseCommand(__FUNCTION__, null, ['id' => $id, 'format' => $format, $format => $data]);
    }

    /**
     * @param  string|int  $id
     * @param  array<string|int, string|int|array<string|int, mixed>>  $data
     * @param  string  $format
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function replace_activity(string|int $id, array $data, string $format = 'xml'): \SimpleXMLElement|array
    {
        return $this->parseCommand(__FUNCTION__, null, ['id' => $id, 'format' => $format, $format => $data]);
    }

    /**
     * @param  string|int  $id
     * @param  array<string|int, string|int|array<string|int, mixed>>  $data
     * @param  string  $format
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function replace_note(string|int $id, array $data, string $format = 'xml'): \SimpleXMLElement|array
    {
        return $this->parseCommand(__FUNCTION__, null, ['id' => $id, 'format' => $format, $format => $data]);
    }

    /**
     * @param  string|int  $id
     * @param  array<string|int, string|int|array<string|int, mixed>>  $data
     * @param  string  $format
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function replace_map(string|int $id, array $data, string $format = 'xml'): \SimpleXMLElement|array
    {
        return $this->parseCommand(__FUNCTION__, null, ['id' => $id, 'format' => $format, $format => $data]);
    }

    /**
     * @param  string|int  $id
     * @param  array<string|int, string|int|array<string|int, mixed>>  $data
     * @param  string  $format
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function replace_directions(string|int $id, array $data, string $format = 'xml'): \SimpleXMLElement|array
    {
        return $this->parseCommand(__FUNCTION__, null, ['id' => $id, 'format' => $format, $format => $data]);
    }

    /**
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function list_trip(?array $filter = null): \SimpleXMLElement|array
    {
        return $this->parseCommand(__FUNCTION__, urlArgs: $filter);
    }

    /**
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function list_object(?array $filter = null): \SimpleXMLElement|array
    {
        return $this->parseCommand(__FUNCTION__, urlArgs: $filter);
    }

    /**
     * @param  array<string, string|int>  $filter
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function list_points_program(?array $filter = null): \SimpleXMLElement|array
    {
        return $this->parseCommand(__FUNCTION__, urlArgs: $filter);
    }

    /**
     * @param  array<string|int, string|int|array<string|int, mixed>>  $data
     * @param  string  $format
     * @return array<int|string, mixed>|\SimpleXMLElement
     * @throws \JsonException
     */
    public function create(array $data, string $format = 'xml'): \SimpleXMLElement|array
    {
        return $this->parseCommand(__FUNCTION__, postArgs: ['format' => $format, $format => $data]);
    }

    /**
     * @return bool|array<string|int, mixed>|string
     */
    public function get_request_token(): bool|array|string
    {
        $response = $this->doRequest('/oauth/request_token');
        if (!is_string($response)) {
            return false;
        }
        if ($this->httpCode === 200) {
            $request_token = [];
            parse_str($response, $request_token);

            return $request_token;
        }

        return $response;
    }

    /**
     * @return bool|array<string|int, mixed>|string
     */
    public function get_access_token(): bool|array|string
    {
        $response = $this->doRequest('/oauth/access_token');
        if (!is_string($response)) {
            return false;
        }
        if ($this->httpCode === 200) {
            $access_token = [];
            parse_str($response, $access_token);

            return $access_token;
        }

        return $response;
    }
}
