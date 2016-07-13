<?php

namespace humanized\scoopit;

/**
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
    public $autoScoopConfig = ['topicSuffix' => '-pool'];

    public function init()
    {
        parent::init();
        if (\Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'humanized\scoopit\cli';
            $this->params['saveSuggestions'] = $this->saveSuggestions;
            if (isset($this->preProcessorClass)) {
                $this->params['postProcessorClass'] = $this->postProcessorClass;
            }
            if (isset($this->postProcessorClass)) {
                $this->params['postProcessorClass'] = $this->postProcessorClass;
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
           //     echo substr($topic->name, -strlen($var)) . '==' . $var . "\n";
                return (substr($topic->name, -strlen($var)) == $var);
            };
            return;
        }
    }

}
