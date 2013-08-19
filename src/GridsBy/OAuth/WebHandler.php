<?php
namespace GridsBy\OAuth;


use AiP\Middleware\URLMap;
use Teapot\HttpResponse\Status\StatusCode;


class WebHandler extends URLMap
{
    private $client;
    private $twig;

    public function __construct()
    {
        $root = realpath(__DIR__.'/../../..');

        $this->client = new Client();
        $this->twig = new \Twig_Environment(new \Twig_Loader_Filesystem($root.'/templates'));

        parent::__construct([
            '/favicon.ico' => function(){ return array(StatusCode::NOT_FOUND, array('Content-type', 'text/plain'), 'Page not found'); },
            '/' => [$this, 'index'],
            '/callback/' => [$this, 'callback'],
        ]);
    }

    public function index($ctx)
    {
        if ($ctx['env']['REQUEST_METHOD'] == 'GET') {
            $data = $this->client->configData();
            $data['config_file'] = $this->client->configPath();

            $body = $this->twig->render('index.twig', $data);

            return [StatusCode::OK, ['Content-type', 'text/html; charset=utf-8'], $body];
        } else {
            if (array_key_exists('action', $ctx['_POST'])) {
                if ($ctx['_POST']['action'] == 'request_token') {
                    $this->client->fetchRequestToken('http://127.0.0.1:8081/callback/');
                    return $this->redirectAfterPost('http://127.0.0.1:8081/');
                } elseif ($ctx['_POST']['action'] == 'access_token') {
                    $authorization_url = $this->client->authorizationUrl();
                    return $this->redirectAfterPost($authorization_url);
                } elseif ($ctx['_POST']['action'] == 'consumer_token') {
                    $this->client->setConsumerCredentials($ctx['_POST']['consumer_token'], $ctx['_POST']['consumer_secret']);
                    return $this->redirectAfterPost('http://127.0.0.1:8081/');
                }
            }

            return [StatusCode::BAD_REQUEST, ['Conent-type', 'text/html; charset=utf-8'], 'Bad request'];
        }
    }

    public function callback($ctx)
    {
        if (isset($ctx['_GET']['oauth_token'])) {
            if (!isset($ctx['_GET']['oauth_verifier'])) {
                return [StatusCode::BAD_REQUEST, ['Content-type', 'text/html'], '<h1>Bad request</h1><p>Verifier is expected</p>'];
            }

            $token = $ctx['_GET']['oauth_token'];
            $verifier = $ctx['_GET']['oauth_verifier'];

            if ($token !== $this->client->configData()['tokens']['request_token']) {
                return [StatusCode::NOT_FOUND, ['Content-type', 'text/plain; charset=utf-8'], 'token not found'];
            }

            try {
                $this->client->fetchAccessToken($verifier);
            } catch (\Exception $e) {
                $body = '<h1>Failed to fetch Access Token</h1><p>'.$e->getMessage().'</p>';
                return [StatusCode::INTERNAL_SERVER_ERROR, ['Content-type', 'text/html'], $body];
            }
        } elseif (isset($ctx['_GET']['denied'])) {
            $token = $ctx['_GET']['denied'];

            if ($token !== $this->client->configData()['tokens']['request_token']) {
                return [StatusCode::NOT_FOUND, ['Content-type', 'text/plain; charset=utf-8'], 'token not found'];
            }

            $this->client->resetRequestToken();
        } else {
            return [StatusCode::BAD_REQUEST, ['Content-type', 'text/html'], '<h1>Bad request</h1>'];
        }

        return $this->redirectAfterPost('http://127.0.0.1:8081/');
    }

    private function redirectAfterPost($url)
    {
        return [
            StatusCode::SEE_OTHER,
            [
                'Content-type', 'text/html; charset=utf-8',
                'Location', $url
            ],
            '<a href="'.$url.'">Click here</a>'
        ];
    }
}
