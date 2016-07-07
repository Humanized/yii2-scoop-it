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

    public $_requestTokenParams = [];
    public $_accessTokenParams = [];
    public $_authorizationResponse = NULL;
    public $_stack;
    private $_middlewareConfig = [];

    /**
     * The c
     * 
     * @param array $config
     */
    public function __construct(array $config = array())
    {

        $this->_initConfig();
        $this->_stack = HandlerStack::create();
        $this->_middlewareConfig = [
            'consumer_key' => Yii::$app->params['scoopit']['consumerKey'],
            'consumer_secret' => Yii::$app->params['scoopit']['consumerSecret'],
            'token' => Yii::$app->params['scoopit']['token'],
            'token_secret' => Yii::$app->params['scoopit']['tokenSecret']
        ];
        $this->_initStack();
        parent::__construct([
            'base_uri' => Yii::$app->params['scoopit']['remoteUri'],
            'handler' => &$this->_stack,
            'auth' => 'oauth'
        ]);
    }

    private function _initConfig()
    {
        $config = Yii::$app->params['scoopit'];
        if (!isset($config)) {
            throw new \yii\base\InvalidConfigException("Accessible params array missing index scoopit");
        }
        if (!isset($config['remoteUri'])) {
            throw new \yii\base\InvalidConfigException("Scoop.it remote configuration missing the remoteUri parameter");
        }
        if (!isset($config['consumerKey'])) {
            throw new \yii\base\InvalidConfigException("Scoop.it remote configuration missing the consumerKey parameter");
        }
        if (!isset($config['consumerSecret'])) {
            throw new \yii\base\InvalidConfigException("Scoop.it remote configuration missing the consumerSecret parameter");
        }
    }

    private function _initStack()
    {
        $middleware = new Oauth1($this->_middlewareConfig);
        $this->_stack->push($middleware);
    }

    private function _processTokenRequestResponse()
    {
        $this->_requestTokenParams = [];
        $requestTokenResponse = $this->post('/oauth/request')->getBody()->getContents();

        foreach (explode('&', $requestTokenResponse) as $queryString) {
            $q = explode('=', $queryString);
            if ($q[0] == "oauth_token") {
                $this->_appendTokenMiddlewareConfig("token", $q[1]);
            }
            if ($q[0] == "oauth_token_secret") {
                $this->_appendTokenMiddleWareConfig("token_secret", $q[1]);
            }
        }
    }

    private function _appendTokenMiddlewareConfig($attrib, $value)
    {
        $this->_middlewareConfig[$attrib] = $value;
    }

    public function getRawSource($topicId, $lastUpdate = 0)
    {
        $raw = $this->get('api/1/topic', ['query' => ['since' => time() - (60 * 60 * $lastUpdate), 'id' => $topicId]
        ]);
        $out = \GuzzleHttp\json_decode($raw->getBody()->getContents())->topic;
        return $out;
    }

    public function getSources($topicId, $lastUpdate = 0)
    {

        return $this->_getSources('curablePosts', $topicId, $lastUpdate);
    }

    public function getScoops($topicId, $lastUpdate = 0)
    {

        return $this->_getSources('curatedPosts', $topicId, $lastUpdate);
    }

    public function _getSources($var, $topicId, $lastUpdate)
    {

        $queryParams = [
            'id' => $topicId,
            'since' => time() - (60 * 60 * $lastUpdate)
        ];
        if ($var == 'curablePosts') {
            $queryParams['curable'] = 100;
            $queryParams['curablePage'] = 100;
        }

        $raw = $this->get('api/1/topic', ['query' => $queryParams
        ]);
        $out = \GuzzleHttp\json_decode($raw->getBody()->getContents())->topic;
        return $out->$var;
    }

    public function getTopics($filerOutput = FALSE)
    {
        $raw = $this->get('api/1/company/topics');

        $out = \GuzzleHttp\json_decode($raw->getBody()->getContents())->topics;
        if ($filerOutput == TRUE) {
            $getDataFn = $this->getTopicFilter();
            $out = array_values(array_filter(array_map($getDataFn, $out)));
        }
        return $out;
    }

    public function getTopicKeywords($topicId, $filerOutput = FALSE)
    {
        $raw = $this->get('api/1/sse', ['query' => [
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
