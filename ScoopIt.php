<?php

namespace humanized\scoopit;

/**
 * 
 * Yii2 Scoop.it module
 * 
 * A small, flexible framework for interfacing to a Scoop.it account.
 * 
 * 
 * @name Yii2 Scoopit Module Class 
 * @version 1.0 
 * @author Jeffrey Geyssens <jeffrey@humanized.be>
 * @package yii2-scoopit
 */
class ScoopIt extends \yii\base\Module
{

    /**
     *
     * @var boolean - when true, a local copy is stored of curable (unpublished) posts 
     */
    public $saveSuggestions = false;

    /**
     *
     * @var boolean - when true, published posts when be removed from the local topic when tagged using #rm 
     */
    public $enableRmTag = true;

    /**
     *
     * @var boolean - when true, published posts when be explicitly updated from the local topic when tagged using #cp 
     */
    public $enableCpTag = true;

    /**
     *
     * @var integer - amount of hours that a post tagged using #rm should remain available remotely. When set to 0, the post is removed remotely immediately after local removal. This setting is useful, to allow multiple local systems to synchronise to the remote system state before actual remote deletion occurs  
     */
    public $remoteLifetime = 0;

    /**
     *
     * @var boolean - when true, automatically published posts will be tagged remotely using #auto 
     */
    public $enableAutoTag = false;

    /**
     *
     * @var boolean - when true, remote double-posts identified by a local system will have remote posts tagged with list of tags #{topic-id|post-id} each tupple identifying a distinct duplicate  
     */
    public $enableDoublePostTags = true;
    public $preProcessorClass;
    public $postprocessorClass;
    public $mapTopic;
    public $autoScoopConfig = ['topicSuffix' => '-pool'];

    public function init()
    {
        parent::init();
        if (\Yii::$app instanceof \yii\console\Application) {
            $this->_initConsole();
        }
    }

    private function _initConsole()
    {
        $this->controllerNamespace = 'humanized\scoopit\cli';
        $this->params['saveSuggestions'] = $this->saveSuggestions;
        $this->params['enableRmTag'] = $this->enableRmTag;
        $this->params['enableCpTag'] = $this->enableCpTag;

        $this->params['remoteLifetime'] = $this->remoteLifetime;
        $this->params['enableDoublePostTags'] = $this->enableDoublePostTags;

        if (isset($this->preProcessorClass)) {
            $this->params['preProcessorClass'] = $this->preProcessorClass;
        }
        if (isset($this->postprocessorClass)) {
            $this->params['postprocessorClass'] = $this->postprocessorClass;
        }

        if (isset($this->mapTopic)) {
            $this->params['mapTopic'] = $this->mapTopic;
        }

        $this->_initAutoScoopConfig();
    }

    private function _initAutoScoopConfig()
    {
        $params = $this->autoScoopConfig;

        if (isset($params['autoScoopTopicCondition'])) {
            $this->params['autoScoopTopicCondition'] = $this->autoScoopConfig['autoScoopCondition'];
            return;
        }
        if (isset($params['topicSuffix'])) {
            $var = $params['topicSuffix'];
            $this->params['autoScoopTopicCondition'] = function($topic) use ($var) {
                return (substr($topic->name, -strlen($var)) == $var);
            };
            return;
        }
    }

}
