<?php

/**
    Twitter Authentication functions modified from:
    http://collaboradev.com/2011/04/01/twitter-oauth-php-tutorial/
 */

namespace Dappur\TwigExtension;
use Dappur\Model\Oauth2Providers;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Slim\Interfaces\RouterInterface;
use Abraham\TwitterOAuth\TwitterOAuth;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Oauth2 extends \Twig_Extension
{
    protected $request;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'oauth2';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('authorize_url', array($this, 'authorizeUrl')),
        );
    }

    public function authorizeUrl($providerId, $clientId)
    {
        $check = Oauth2Providers::find($providerId);
        $oauthUtils = new \Dappur\Dappurware\Oauth2($this->container);

        if ($check) {
            $state = null;
            if ($this->container->session->exists('oauth2-state')) {
                $state = $this->container->session->get('oauth2-state');
            }

            $redirectUri = $this->container->request->getUri()->getBaseUrl() .
                $this->container->router->pathFor('oauth', array('slug' => $check->slug));

            switch ($check->slug) {
                case 'twitter':
                    $twConnection = new \Abraham\TwitterOAuth\TwitterOAuth(
                        $this->container->settings['oauth2']['twitter']['api_key'],
                        $this->container->settings['oauth2']['twitter']['api_secret']
                    );

                    $requestToken = $twConnection->oauth(
                        'oauth/request_token', [
                            'oauth_callback' => $redirectUri
                        ]
                    );

                    $authorizeUrl = $twConnection->url(
                        'oauth/authorize', [
                            'oauth_token' => $requestToken['oauth_token']
                        ]
                    );
                    break;
                default:
                    $queryParams = urldecode(http_build_query(array(
                        "client_id" => $clientId,
                        "redirect_uri" => $redirectUri,
                        "scope" => $check->scopes,
                        "state" => $state,
                        "response_type" => "code",
                        "access_type" => "offline",
                        "prompt" => "consent"
                    )));

                    $authorizeUrl = $check->authorize_url . "?" . $queryParams;

                    
                    break;
            }

            return $authorizeUrl;
        }
        
        return false;
    }
}
