<?php

namespace App\Services\Vsemayki;

use App\Models\Connect;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Support\Facades\DB;

/**
 * Class RestConnector
 */
class RestConnectorNew
{
    private $url = 'https://rest.vsemayki.ru';
    private $clientId;
    private $clientSecret;
    private $headers = [
        'headers' => [
        'Cache-Control' => 'no-cache',
        'Accept' => '*/*',
        'Accept-Encoding' => 'gzip, deflate, br',
        'Connection' => 'keep-alive',
        ],
    ];

    
    public $token = false;

    public function __construct($clientId, $clientSecret)
    {
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * @param string $url
     * @param array  $params
     * @param string $method
     *
     * @return mixed
     * @throws \Exception
     */
    public function sendRequest($url, array $params = [], $method = 'GET'): mixed
    {
        $result = $this->makeRequest($url, $params, $method, $this->getToken());

        if (!in_array($result['code'], [200, 201, 204], false)) {
            if (in_array($result['code'], [401, 403], false)) {
                $this->updateToken();

                return $this->sendRequest($url, $params, $method);
            }
        }

        return $result['body'] ?: [];
    }

    /**
     * @param string $url
     * @param array  $params
     * @param string $method
     * @param null   $token
     *
     * @return array
     */
    private function makeRequest($url, array $params = [], $method = 'GET', $token = null): array
    {
        try {
            $options = [];
            $client  = new Guzzle(['base_uri' => $this->url]);


            if ($token) {
                $options = [
                    'query' => ['access-token' => $token ],
                ];

            }

            switch ($method) {
                case 'POST':
                case 'PUT':
                    $options = array_merge($options, ['form_params' => $params]);
                    break;
                case 'GET':
                case 'DELETE':
                    $options = ['query' => array_merge($options['query'], $params)];
                    break;
            }

            
            $options = array_merge($options, $this->headers);

            $options = array_merge($options, ['connect_timeout' => 60.]);
        

            $requestResult = $client->request($method, $url, $options);


            $result        = [
                'code' => $requestResult->getStatusCode(),
                'body' => json_decode($requestResult->getBody()),
            ];

        } catch (ClientException $e) {
            $result = [
                'code' => $e->getCode(),
                'body' => $e->getMessage(),
            ];
        }

        return $result;
    }


    public function getToken(): bool|string
    {
        if(!$this->token) {
            $query = DB::table('connects')->select('token')->first(); 
            $this->token = $query->token;

        }

        return ($this->token) ?: $this->updateToken();
    }


    public function updateToken(): bool|string
    {
        $result = $this->makeRequest('/oauth2/token', [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'client_credentials'
        ], 'POST');

        $this->token = $result['body']->access_token;

        Connect::query()
            ->truncate()
            ->create(['token' => $result['body']->access_token]);


        return $this->token;
    }
}