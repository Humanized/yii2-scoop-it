<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace humanized\scoopit\cli;

use yii\console\Controller;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Yii;
use yii\helpers\Console;

/**
 * A CLI port of the Yii2 RBAC Manager Interface.
 *
 * 
 * @name Scoop.it connection test CLI 
 * @version 0.1
 * @author Jeffrey Geyssens <jeffrey@humanized.be>
 * @package yii2-scoopit
 *
 */
class OauthController extends Controller
{

    private $_callbackUri;
    private $_params = [];
    private $_middlewareConfig = [];

    public function actionIndex()
    {
        $this->_initConfig();
        //Leg #1: Obtain application request token 
        $requestTokenResponse = $this->_processRequestToken();
        //Leg #2: Obtain authorisation verifier
        $authorisationVerfier = $this->_promptAuthorisationVerfifier($requestTokenResponse);
        //Leg #3: 
        $this->_updateMiddleware($requestTokenResponse, $authorisationVerfier);
        $accessTokenResponse = $this->_processAccessToken();
        $this->_out($accessTokenResponse);
        return 0;
    }

    private function _initConfig()
    {
        $this->_params = Yii::$app->params['scoopit'];
        Yii::$app->urlManager->setScriptUrl($this->_params['authorisationCallbackUri']);
        $this->_callbackUri = \yii\helpers\Url::toRoute(['/' . $this->module->id . "/default/oauth-callback"], true);
        //echo $this->_callbackUri . "\n";
        $this->_middlewareConfig = [
            'consumer_key' => $this->_params['consumerKey'],
            'consumer_secret' => $this->_params['consumerSecret'],
            'token' => false,
            'token_secret' => false,
        ];
    }

    private function _processRequestToken()
    {
        $requestTokenClient = $this->_getClient($this->_middlewareConfig);
        return $requestTokenClient->post('/oauth/request')->getBody()->getContents();
    }

    private function _promptAuthorisationVerfifier($requestTokenResponse)
    {
        $requestTokenResponse .= '&oauth_callback=' . $this->_callbackUri;
        $authorisationTokenUrl = $this->_params['remoteUri'] . '/oauth/authorize?' . $requestTokenResponse;
        $this->stdout('Follow link to authorise: ');
        $this->stdout($authorisationTokenUrl . "\n", Console::FG_GREEN);
        return $this->prompt('Enter Verifier');
    }

    private function _updateMiddleware($requestTokenResponse, $authorisationVerfier)
    {
        foreach (explode('&', $requestTokenResponse) as $queryString) {
            $q = explode('=', $queryString);
            if ($q[0] == "oauth_token" || $q[0] == "oauth_token_secret") {
                $this->_middlewareConfig[str_replace('oauth_', '', $q[0])] = $q[1];
            }
        }
        $this->_middlewareConfig['verifier'] = $authorisationVerfier;
    }

    private function _processAccessToken()
    {
        $accessTokenClient = $this->_getClient();
        return $accessTokenClient->post('/oauth/access')->getBody()->getContents();
    }

    private function _getClient()
    {
        $stack = HandlerStack::create();
        $middleware = new Oauth1($this->_middlewareConfig);
        $stack->push($middleware);
        return new GuzzleClient([
            'base_uri' => $this->_params['remoteUri'],
            'handler' => $stack,
            'auth' => 'oauth'
        ]);
    }

    private function _out($accessTokenResponse)
    {
        $this->stdout(str_replace('&', "\n", $accessTokenResponse) . "\n");
    }

}
