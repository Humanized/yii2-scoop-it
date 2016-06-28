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

    public function getSources($topicId, $lastUpdate = 0)
    {

        return $this->_getSources('curatedPosts', $topicId, $lastUpdate);
    }

    public function getScoops($topicId, $lastUpdate = 0)
    {

        return $this->_getSources('curatedPosts', $topicId, $lastUpdate);
    }

    public function _getSources($var, $topicId, $lastUpdate)
    {
        $raw = $this->get('topic', ['query' => ['since' => time() - (60 * 60 * $lastUpdate), 'curable' => 50, 'curablePage' => 50, 'id' => $topicId]
        ]);
        $out = \GuzzleHttp\json_decode($raw->getBody()->getContents())->topic;
        return $out->$var;
    }

    public function getTopics($filerOutput = FALSE)
    {
        $raw = $this->get('company/topics');

        $out = \GuzzleHttp\json_decode($raw->getBody()->getContents())->topics;
        if ($filerOutput == TRUE) {
            $getDataFn = $this->getTopicFilter();
            $out = array_values(array_filter(array_map($getDataFn, $out)));
        }
        return $out;
    }

    public function getTopicKeywords($topicId, $filerOutput = FALSE)
    {
        $raw = $this->get('sse', ['query' => [
                'topic' => $topicId
        ]]);

        $out = \GuzzleHttp\json_decode($raw->getBody()->getContents())->keywords;

        return $out;
    }

    public function getTopicFilter()
    {

        $filter = function($topic) {
            return true;
        };
        $isSetFilter = isset(Yii::$app->params['scoopit']['topicOptions']) && isset(Yii::$app->params['scoopit']['topicOptions']['importFilter']);
        if ($isSetFilter) {

            $filter = Yii::$app->params['scoopit']['topicOptions']['importFilter'];
        }
        $importTopic = function($topic) use ($filter) {
            //Remove condition when using real account
            if ($filter($topic)) {
                //  echo $topic->shortName . "\n";
                return ["id" => $topic->id, "name" => $topic->shortName];
            }
        };
        return $importTopic;
    }

}
