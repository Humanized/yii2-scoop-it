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
use \humanized\scoopit\models\Source;
use \humanized\scoopit\models\Scoop;
use \humanized\scoopit\models\Tag;
use yii\helpers\Console;

/**
 * A CLI port of the Yii2 RBAC Manager Interface.
 *
 * 
 * @name Scoop.it CLI Data Synchronisation Tool
 * @version 0.1
 * @author Jeffrey Geyssens <jeffrey@humanized.be>
 * @package yii2-scoopit
 *
 */
class DataController extends Controller
{

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

    public function actionIndex($lastUpdate)
    {
        $client = new Client();
        $topics = $client->getTopics(TRUE);
        foreach ($topics as $topic) {
            $this->actionSync($topic['id']);
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
        $topic = \humanized\scoopit\models\Topic::findOne(!is_numeric($topicId) ? ['name' => $topicId] : $topicId);
        if (NULL === $topic) {
            $this->stdout("No Such Topic \n");
            return 1;
        }
        $client = new Client();
        $isPool = !(FALSE === strpos($topic->name, 'pool'));

        //Pass #1: Obtain all scoops related to the topic 
        $scoops = $client->getScoops($topic->id, $lastUpdate);

        foreach ($scoops as $scoop) {
            $this->_import($scoop, $topic->id, TRUE);
        }

        //Pass #2: Obtain all sources related to the topic 
        // $sources = $client->getSources($topic->id, $lastUpdate);
        /*
          foreach ($sources as $data) {
          $source = new Source();
          $source->setPostAttributes($data);
          $source->save();
          }
         * 
         */
        return 0;
    }

    private function _process()
    {
        
    }

    private function _import($item, $topicId, $withScoop = FALSE)
    {
        $this->stdout("\nProcessing Source: ");
        $this->stdout($item->url . "\n", Console::FG_GREEN, Console::BOLD);
        $model = Source::findOne($item->id);
        if (!isset($model)) {
            $model = new Source();
            $model->setPostAttributes($item);
            try {
                $model->save();
            } catch (\Exception $ex) {
                
            }
        }

        if (isset($model)) {
            $this->_initPostProcessor('afterTopicLink', $model, 'topicPostProcessor');
            $topicData = $model->linkTopic($topicId);
            $topic = $topicData[1];
            $this->stdout((!$topicData[0] ? 'Already ' : '') . 'Linked to topic: ', (!$topicData[0] ? Console::FG_RED : Console::FG_GREEN), Console::BOLD);
            $this->stdout($topic->name . "\n");


            if ($withScoop) {
                $this->_importScoop($item);
                $this->_importTags($item);
            }
        }
        return $model->id;
    }

    private function _importTags($item)
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

    private function _importScoop($item)
    {
        $model = Scoop::findOne($item->id);
        if (!isset($model)) {
            $model = new Scoop();
            $this->_initPostProcessor('afterScoop', $model, 'postProcessor');
            $model->setPostAttributes($item);
            try {
                if ($model->save()) {
                    $this->stdout('New Scoop.it Scoop Imported' . "\n");
                    return;
                }
                VarDumper::dump($model->errors);
            } catch (\Exception $ex) {
                
            }
        }
    }

    public function actionPatchDates()
    {
        foreach (Scoop::find()->all() as $scoop) {
            $scoop->date_published=$scoop->source->date_retrieved;
            $scoop->save();
        }
    }

}
