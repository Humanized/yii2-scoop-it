<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace humanized\scoopit\cli;

use Yii;
use yii\console\Controller;
use humanized\scoopit\Client;
use yii\helpers\VarDumper;
use humanized\scoopit\models\Source;
use humanized\scoopit\models\Scoop;
use humanized\scoopit\models\Tag;
use humanized\scoopit\models\Topic;
use yii\helpers\Console;

/**
 * Provides an interface to locally syncronise remote scoop.it topic data
 *
 * @name Scoop.it CLI Data Synchronisation Tool
 * @version 0.1
 * @author Jeffrey Geyssens <jeffrey@humanized.be>
 * @package yii2-scoopit
 *
 */
class DataController extends Controller
{

    /**
     * 
     * @param type $lastUpdate
     * @return int
     */
    public function actionIndex($lastUpdate)
    {
        $client = new Client();
        $topics = $client->getTopics(TRUE);
        foreach ($topics as $topic) {
            $this->actionSync($topic['id'], $lastUpdate);
        }
        return 0;
    }

    /**
     * 
     * @param type $topicId
     * @param type $lastUpdate
     * @return int
     */
    public function actionSync($topicId, $lastUpdate)
    {
        $topic = Topic::findOne(!is_numeric($topicId) ? ['name' => $topicId] : $topicId);
        if (NULL === $topic) {
            $this->stdout("No Such Topic \n");
            return 1;
        }

        $this->stdout('processing topic: ');
        $this->stdout($topic->name . "\n", Console::FG_GREEN, Console::BOLD);

        $client = new Client();

        //Auto scoop when condition is satisfied
        if (isset($this->module->params['autoScoopTopicCondition']) && call_user_func($this->module->params['autoScoopTopicCondition'], $topic)) {
            //     $this->stdout("auto-scooping \n");
            $client->autoScoop($topic->id, $lastUpdate);
        }
        //Syncronise Scoops
        $scoops = $client->getScoops($topic->id, $lastUpdate);
        // $this->stdout("saving scoops to local storage \n");
        foreach ($scoops as $scoop) {
            $model = $this->_sync($scoop, $topic, TRUE);
        }
        //Store suggestions
        if (isset($this->module->params['saveSuggestions']) && $this->module->params['saveSuggestions']) {
            $this->stdout("saving suggestions to local storage \n");
            foreach ($client->getSources($topic->id, $lastUpdate) as $source) {
                $source = $this->_sync($source, $topic, FALSE);
            }
        }

        return 0;
    }

    /**
     * 
     * @param type $item
     * @param type $topicId
     * @param type $scooped
     */
    private function _sync($item, $topic, $scooped = TRUE)
    {
        $topicId = $topic->id;
        $this->stdout("\n Processing Source: ");
        $this->stdout($item->url . "\n", Console::FG_GREEN, Console::BOLD);

        $source = Source::findItem($item);
        if (!isset($source)) {
            $source = Source::create($item);
        }
        if (isset($source)) {
            $this->_linkTopic($source, $topicId);
            if ($scooped) {
                $this->_syncScoop($item);
                $this->_syncScoopTags($item);
            }
            if (isset($this->module->params['postProcessorClass']) && method_exists($this->module->params['postProcessorClass'], 'afterSync')) {
                call_user_func([$this->module->params['postProcessorClass'], 'afterSync'],$topic,$source);
               
            }
        }
        return $source;
    }

    private function _linkTopic($model, $topicId)
    {
        $this->_initPostProcessor('afterTopicLink', $model, 'topicPostProcessor');
        $topicData = $model->linkTopic($topicId);
        $topic = $topicData[1];
        $this->stdout((!$topicData[0] ? 'Already ' : '') . 'Linked to topic: ', (!$topicData[0] ? Console::FG_RED : Console::FG_GREEN), Console::BOLD);
        $this->stdout($topic->name . "\n");
    }

    private function _syncScoop($item)
    {
        $model = Scoop::findOne($item->id);
        if (!isset($model)) {
            $model = new Scoop();
            $this->_initPostProcessor('afterScoop', $model, 'postProcessor');
            $model->setPostAttributes($item);
            try {
                if ($model->save()) {
                    $this->stdout('Scoop Imported' . "\n");
                    return;
                }
                VarDumper::dump($model->errors);
            } catch (\Exception $ex) {
                
            }
        }
    }

    private function _syncScoopTags($item)
    {
        $scoop = Scoop::findOne($item->id);
        if (isset($scoop)) {
            $this->_initPostProcessor('afterScoopTag', $scoop, 'tagPostProcessor');
            foreach ($item->tags as $tag) {
                $model = Tag::findOne(['name' => $tag]);
                if (!isset($model)) {
                    $model = new Tag(['name' => $tag]);
                    $model->save();
                }
                $scoop->linkTag($model->id);
            }
        }
    }

    private function _initPostProcessor($fnName, $model, $postProcessor)
    {
        if (isset($this->module->params['postProcessorClass']) && method_exists($this->module->params['postProcessorClass'], $fnName)) {
            $fn = [$this->module->params['postProcessorClass'], $fnName];
            $model->$postProcessor = $fn;
        }
    }

    public function actionDropAll()
    {
        Source::deleteAll('1=1');
    }

    public function peek($topicId, $mode = 1)
    {
        $topic = \humanized\scoopit\models\Topic::findOne(!is_numeric($topicId) ? ['name' => $topicId] : $topicId);
        if (NULL === $topic) {
            $this->stdout("No Such Topic \n");
            return 1;
        }
        $client = new Client();
    }

}
