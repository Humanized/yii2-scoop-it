<?php

namespace humanized\scoopit;

use Yii;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Client extends \GuzzleHttp\Client
{

    /**
     * The c
     * 
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $config = Yii::$app->params['scoopit'];
        if (!isset($config)) {
            throw new \yii\base\InvalidConfigException("Accessible params array missing index viajero");
        }
        if (!isset($config['remoteUri'])) {
            throw new \yii\base\InvalidConfigException("Viajero remote configuration missing the remoteUri parameter");
        }
        if (!isset($config['remoteConsumerKey'])) {
            throw new \yii\base\InvalidConfigException("Viajero remote configuration missing the remoteAccessToken parameter");
        }
        if (!isset($config['remoteSecretKey'])) {
            throw new \yii\base\InvalidConfigException("Viajero remote configuration missing the remoteAccessToken parameter");
        }
        $stack = HandlerStack::create();
        $middleware = new Oauth1([
            'consumer_key' => Yii::$app->params['scoopit']['remoteConsumerKey'],
            'consumer_secret' => Yii::$app->params['scoopit']['remoteSecretKey'],
            'token_secret' => '',
        ]);
        $stack->push($middleware);
        parent::__construct([
            'base_uri' => Yii::$app->params['scoopit']['remoteUri'],
            'handler' => $stack,
            'auth' => 'oauth'
        ]);
    }

}
