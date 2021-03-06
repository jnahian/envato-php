<?php 

namespace Nahid\EnvatoPHP\Api;

use duncan3dc\Sessions\SessionInstance;
use Nahid\EnvatoPHP\HttpManager\RequestHandler;
use Nahid\EnvatoPHP\HttpManager\Response;

abstract class AbstractApi
{

    protected $client;
    protected $parameters= [];
    protected $session;
    protected $config;
    protected $clientId;
    protected $clientSecret;
    //protected $personalToken;
    protected $redirectUri;
    private $requestMethods = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'HEAD',
        'OPTIONS',
        'PATCH'
    ];


    function __construct()
    {
        $this->clientId = $this->config['client_id'];
        $this->clientSecret = $this->config['client_secret'];
        //$this->personalToken = $this->config['personal_token'];
        $this->redirectUri = $this->config['redirect_uri'];

        $this->client = new RequestHandler();
        $this->session = new SessionInstance($this->config['app_name']);
    }

    public function __call($func, $params)
    {
        $method = strtoupper($func);

        if (in_array($method, $this->requestMethods)) {
            $parameters[] = $method;
            $parameters[] = $params[0];
            return call_user_func_array([$this, 'makeMethodRequest'], $parameters);
        }
    }


    public function formParams($params = array())
    {
        if (is_array($params)) {
            $this->parameters['form_params'] = $params;
            return $this;
        }

        return false;
    }

    public function headers($params = array())
    {
        if (is_array($params)) {
            $this->parameters['headers'] = $params;
            return $this;
        }

        return false;
    }

    public function query($params = array())
    {
        if (is_array($params)) {
            $this->parameters['query'] = $params;
            return $this;
        }

        return false;
    }

    public function makeMethodRequest($method, $uri)
    {
        $this->parameters['timeout'] = 60;
        $defaultHeaders = [
            'User-Agent'=>$_SERVER['HTTP_USER_AGENT'],
            'Authorization'=> 'Bearer ' . $this->session->get('access_token')
        ];

        if ($method == 'GET') {
            if (isset($this->parameters['headers'])) {
                $this->parameters['headers'] = array_merge($defaultHeaders , $this->parameters['headers']);
            } else {
                $this->parameters['headers'] = $defaultHeaders;
            }
            
        }


        $response = new Response($this->client->http->request($method, $uri, $this->parameters));

        return $response;
    }

}
