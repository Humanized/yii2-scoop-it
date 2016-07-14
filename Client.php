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
    private $_pager = 1;

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
            'token' => isset(Yii::$app->params['scoopit']['token']) ? Yii::$app->params['scoopit']['token'] : NULL,
            'token_secret' => isset(Yii::$app->params['scoopit']['tokenSecret']) ? Yii::$app->params['scoopit']['tokenSecret'] : NULL
        ];
        $middleware = new Oauth1($this->_middlewareConfig);
        $this->_stack->push($middleware);
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
        $this->_pager = 1;
    }

    public function incrementPager()
    {

        $this->_pager +=1;
        echo 'pager-set:' . $this->_pager . "\n";
    }

    public function resetPager()
    {
        $this->_pager = 1;
        echo 'pager-set:' . $this->_pager . "\n";
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

    public function autoScoop($topicId, $lastUpdate)
    {
        $remoteSources = $this->getSources($topicId, $lastUpdate);
        $queryParams = ['action' => 'accept', 'id' => $topicId, 'directLink' => 0];
        foreach ($remoteSources as $remoteSource) {
            $queryParams['id'] = $remoteSource->id;
            $this->post('api/1/post', ['query' => $queryParams]);
        }
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
        return $this->_getContent('curablePosts', $topicId, $lastUpdate);
    }

    public function getScoops($topicId, $lastUpdate = 0)
    {
        return $this->_getContent('curatedPosts', $topicId, $lastUpdate);
    }

    private function _getContent($from, $topicId, $lastUpdate)
    {
        $queryParams = [
            // 'page' => $this->_pager,
            'id' => $topicId,
            'since' => time() - (60 * 60 * $lastUpdate)
        ];
        if ($from == 'curablePosts') {
            $queryParams['curable'] = 150;
            $queryParams['curablePage'] = $this->_pager;
        } elseif ($from == 'curatedPosts') {
            $queryParams['curated'] = 150;
        }
        $raw = $this->get('api/1/topic', [
            'query' => $queryParams
        ]);
        $out = \GuzzleHttp\json_decode($raw->getBody()->getContents())->topic;
        return $out->$from;
    }

}
