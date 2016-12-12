<?php

namespace humanized\scoopit;

/**
 * 
 * Yii2 Scoop.it module
 * 
 * A small, flexible framework for interfacing with a Scoop.it account.
 * 
 * Provides following functionality:
 * 
 * Data Acquisition:
 * - Setup CLI: Topic synchronisation and internal linkage
 * - Data CLI: Content synchronisation
 * 
 * GUI
 * 
 * 
 * 
 * 
 * @name Yii2 Scoopit Module Class 
 * @version 0.1 
 * @author Jeffrey Geyssens <jeffrey@humanized.be>
 * @package yii2-scoopit
 */
class ScoopIt extends \yii\base\Module
{

    public $saveSuggestions = false;
    public $preProcessorClass;
    public $postProcessorClass;
    public $mapTopic;
    public $autoScoopConfig = ['topicSuffix' => '-pool'];

    public function init()
    {
        parent::init();
        if (\Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'humanized\scoopit\cli';
            $this->params['saveSuggestions'] = $this->saveSuggestions;

            if (isset($this->preProcessorClass)) {
                $this->params['preProcessorClass'] = $this->preProcessorClass;
            }
            if (isset($this->postProcessorClass)) {
                $this->params['postProcessorClass'] = $this->postProcessorClass;
            }

            if (isset($this->mapTopic)) {
                $this->params['mapTopic'] = $this->mapTopic;
            }

            $this->_initAutoScoopConfig();
        }
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
