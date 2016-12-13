<?php

namespace humanized\scoopit;

use Yii;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

/**
 * PHP Scoop.it HTTP Client
 * 
 * The third-party tool provides an API operation allowing access to the raw data, using programming code.
 * The operations supported by the API are listed on http://www.scoop.it/dev/api/1/
 * 

 */
class Client extends \GuzzleHttp\Client
{

    /**
     *
     * @var type 
     */
    public $_requestTokenParams = [];

    /**
     *
     * @var type 
     */
    public $_accessTokenParams = [];

    /**
     *
     * @var type 
     */
    public $_authorizationResponse = NULL;

    /**
     *
     * @var type 
     */
    public $_stack;

    /**
     *
     * @var type 
     */
    private $_middlewareConfig = [];

    /**
     *
     * @var type 
     */
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

    public function deleteScoop($postId)
    {
        $queryParams = ['action' => 'delete', 'id' => $postId];
        $this->post('api/1/post', ['query' => $queryParams]);
    }

    public function autoScoop($topicId, $lastUpdate)
    {
        $remoteSources = $this->_getContent('curablePosts', $topicId, $lastUpdate);
        $queryParams = ['action' => 'accept', 'topicId' => $topicId, 'directLink' => 0];
        foreach ($remoteSources as $remoteSource) {
            $queryParams['id'] = $remoteSource->id;
            $this->post('api/1/post', ['query' => $queryParams]);
        }
    }

    public function curablePosts($topicId, $lastUpdate = 0)
    {
        echo 'curables';
        return $this->_getContent('curablePosts', $topicId, $lastUpdate);
    }

    public function curatedPosts($topicId, $lastUpdate = 0)
    {
        echo 'curated';
        return $this->_getContent('curatedPosts', $topicId, $lastUpdate);
    }

    private function _getContent($node, $topicId, $lastUpdate)
    {
        if ($node != 'curablePosts' && $node != 'curatedPosts') {
            return [];
        }

        $queryParam = str_replace("Posts", "", $node);
        $queryParams = [
            'id' => $topicId,
            'since' => time() - (60 * 60 * $lastUpdate),
            $queryParam => 100,
        ];
        $raw = $this->get('api/1/topic', [
            'query' => $queryParams
        ]);

        $result = \GuzzleHttp\json_decode($raw->getBody()->getContents())->topic;
        return $result->$node;

        /*



          $queryParams = [
          // 'page' => $this->_pager,
          'id' => $topicId,
          'since' => time() - (60 * 60 * $lastUpdate)
          ];
          if ($resource == 'curablePosts') {
          $queryParams['curable'] = 100;
          $queryParams['curablePage'] = $this->_pager;
          } elseif ($resource == 'curatedPosts') {
          $queryParams['curated'] = 100;
          }
          $raw = $this->get('api/1/topic', [
          'query' => $queryParams
          ]);
          $out = \GuzzleHttp\json_decode($raw->getBody()->getContents())->topic;
          return $out->$from;
         * 
         */
    }

    public function getRawSource($topicId, $lastUpdate = 0)
    {
        $raw = $this->get('api/1/topic', ['query' => ['since' => time() - (60 * 60 * $lastUpdate), 'id' => $topicId]
        ]);
        $out = \GuzzleHttp\json_decode($raw->getBody()->getContents())->topic;
        return $out;
    }

    public function getPost($postId)
    {
        $data = \GuzzleHttp\json_decode($this->get('api/1/post', ['query' => ['id' => $postId]
                ])->getBody()->getContents());
        return $data;
    }

    public function getTags($postId)
    {
        
    }

}
