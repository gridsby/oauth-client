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
            $body = $this->twig->render('index.twig', $this->client->configData());
        } else {
            if (array_key_exists('action', $ctx['_POST'])) {
                if ($ctx['_POST']['action'] == 'request_token') {
                    $this->client->fetchRequestToken('http://127.0.0.1:8081/callback/');
                    return $this->redirectAfterPost('http://127.0.0.1:8081/');
                } elseif ($ctx['_POST']['action'] == 'access_token') {
                    $authorization_url = $this->client->authorizationUrl();
                    return $this->redirectAfterPost($authorization_url);
                }
            }
        }

        return [StatusCode::OK, ['Content-type', 'text/html; charset=utf-8'], $body];
    }

    public function callback($ctx)
    {
        $token = $ctx['_GET']['oauth_token'];
        $verifier = $ctx['_GET']['oauth_verifier'];

        if ($token !== $this->client->configData()['tokens']['request_token']) {
            return [StatusCode::NOT_FOUND, ['Content-type', 'text/plain; charset=utf-8'], 'token not found'];
        }

        $this->client->fetchAccessToken($verifier);

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
