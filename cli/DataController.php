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

        $this->stdout('processing: ');
        $this->stdout($topic->name . "\n", Console::FG_GREEN, Console::BOLD);

        $client = new Client();

        //Auto scoop when condition is satisfied
        if (isset($this->module->params['autoScoopTopicCondition']) && call_user_func($this->module->params['autoScoopTopicCondition'], $topic)) {
            $this->stdout("auto-scooping \n");
            $client->autoScoop($topic->id, $lastUpdate);
        }
        //Syncronise Scoops

        $scoops = $client->getScoops($topic->id, $lastUpdate);
        $this->stdout("saving scoops to local storage \n");
        foreach ($scoops as $scoop) {

            $this->_sync($scoop, $topic->id, TRUE);
        }

        //Store suggestions
        if (isset($this->module->params['saveSuggestions']) && $this->module->params['saveSuggestions']) {
            $this->stdout("saving suggestions to local storage \n");
            foreach ($client->getSources($topic->id, $lastUpdate) as $source) {
        
                $this->_sync($source, $topic->id, FALSE);
            }
        }


        /*
          //Import curated posts
          //Import curable posts
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

    /**
     * 
     * @param type $item
     * @param type $topicId
     * @param type $scooped
     */
    private function _sync($item, $topicId, $scooped = TRUE)
    {
        $this->stdout("\nSynchronising Source: ");
        $this->stdout($item->url . "\n", Console::FG_GREEN, Console::BOLD);

        $model = Source::findItem($item);
        if (!isset($model)) {
            $model = Source::create($item);
        }
        if (isset($model)) {
            $this->_linkTopic($model, $topicId);
            if ($scooped) {
                $this->_syncScoop($item);
                $this->_syncScoopTags($item);
            }
        }
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

    private function _import()
    {
        
    }

    /*

      private function _import($item, $topicId, $withScoop = FALSE)
      {
      $this->stdout("\nImport Source: ");
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
     * 
     */

    private function _initPostProcessor($fnName, $model, $postProcessor)
    {
        if (isset($this->module->params['postProcessorClass']) && method_exists($this->module->params['postProcessorClass'], $fnName)) {
            $fn = [$this->module->params['postProcessorClass'], $fnName];
            $model->$postProcessor = $fn;
        }
    }

}
