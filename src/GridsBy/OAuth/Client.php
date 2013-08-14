<?php
namespace GridsBy\OAuth;

use Symfony\Component\Yaml\Yaml;

class Client
{
    private $config_path;
    private $config_data;

    public function __construct($config_path = null)
    {
        $this->config_path = $config_path ?: getenv('HOME').'/.gridsby-oauth.yaml';
        $this->readConfig();
    }

    public function readConfig()
    {
        static $defaults = [
            'urls' => [
                'request_token_url' => 'https://api.grids.by.local/oauth/request_token',
                'authorization_url' => 'https://dev.grids.by.local/oauth/authorize',
                'access_token_url' =>  'https://api.grids.by.local/oauth/access_token',
            ],
            'tokens' => [
                'consumer_token' => null,
                'consumer_secret' => null,
                'request_token' => null,
                'request_secret' => null,
                'access_token' => null,
                'access_secret' => null,
            ],
            'options' => [
                'check_ssl' => false,
            ]
        ];

        if (!file_exists($this->config_path)) {
            $yaml_data = Yaml::dump($defaults);
            file_put_contents($this->config_path, $yaml_data);
        }

        $data = Yaml::parse($this->config_path);
        $this->config_data = array_replace_recursive($defaults, $data);
    }

    public function writeConfig()
    {
        $yaml_data = Yaml::dump($this->config_data);
        file_put_contents($this->config_path, $yaml_data);
    }

    public function configData()
    {
        return $this->config_data;
    }

    public function fetchRequestToken()
    {
        $this->requireConsumer();
        $oauth = $this->initOAuth();

        $response = $oauth->getRequestToken($this->config_data['urls']['request_token_url']);
        if (false === $response) {
            throw new \RuntimeException("Failed fetching request token, response was: " . $oauth->getLastResponse());
        }

        $this->config_data['tokens']['request_token'] = $response['oauth_token'];
        $this->config_data['tokens']['request_secret'] = $response['oauth_token_secret'];
        $this->writeConfig();
    }

    public function authorizationUrl()
    {
        $this->requireRequestToken();

        $root = $this->config_data['urls']['authorization_url'];
        $request_token = $this->config_data['tokens']['request_token'];

        return $root.'?'.http_build_query(['oauth_token' => $request_token]);
    }

    public function fetchAccessToken($verifier)
    {
        $this->requireConsumer();
        $this->requireRequestToken();

        $oauth = $this->initOAuth();

        $tkns = $this->config_data['tokens'];
        $oauth->setToken($tkns['request_token'], $tkns['request_secret']);

        $response = $oauth->getAccessToken($this->config_data['urls']['access_token_url'], '', $verifier);
        if (false === $response) {
            throw new \RuntimeException("Failed fetching access token, response was: " . $oauth->getLastResponse());
        }

        $this->config_data['tokens']['access_token'] = $response['oauth_token'];
        $this->config_data['tokens']['access_secret'] = $response['oauth_token_secret'];
        $this->config_data['tokens']['request_token'] = null;
        $this->config_data['tokens']['request_secret'] = null;
        $this->writeConfig();
    }


    private function requireConsumer()
    {
        $tkns = $this->config_data['tokens'];

        if (
            is_null($tkns['consumer_token']) or
            is_null($tkns['consumer_secret'])
        ) {
            throw new \LogicException('You should register your app at https://dev.grids.by.local/apps/ and set consumer_token and consumer_secret in '.$this->config_path);
        }
    }

    private function requireRequestToken()
    {
        $tkns = $this->config_data['tokens'];

        if (
            is_null($tkns['request_token']) or
            is_null($tkns['request_secret'])
        ) {
            throw new \LogicException("It looks like you didn't get request token yet");
        }
    }

    private function initOAuth()
    {
        $tkns = $this->config_data['tokens'];

        $oauth = new \OAuth(
            $tkns['consumer_token'],
            $tkns['consumer_secret'],
            OAUTH_SIG_METHOD_HMACSHA1, OAUTH_AUTH_TYPE_AUTHORIZATION
        );

        if (array_key_exists('options', $this->config_data)) {
            if (array_key_exists('check_ssl', $this->config_data['options']) and false === $this->config_data['options']['check_ssl']) {
                $oauth->disableSSLChecks();
            }
        }

        return $oauth;
    }
}
