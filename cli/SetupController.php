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
use humanized\scoopit\models\Topic;
use humanized\scoopit\models\TopicMap;
use humanized\scoopit\models\Keyword;

/**
 * Scoopit Setup
 *
 * 
 * @name Scoop.it CLI Setup Tool
 * @version 0.1
 * @author Jeffrey Geyssens <jeffrey@humanized.be>
 * @package yii2-scoop.it
 * 
 */
class SetupController extends Controller
{

    /**
     *
     * @var Client 
     */
    private $_client;
    private $_local;

    /**
     *
     * @var string 
     */
    private $_postprocessorClass = null;

    public function actionWip()
    {
        $this->_client = new Client();
        var_dump($this->_client->getTopic('ddq'));

        //var_dump($this->_client->getTopic('nano-health'));
    }

    public function actionIndex($publish = 0)
    {
        if ($publish != 1 && $publish != 0) {
            throw new \InvalidArgumentException('provided publish parameter must be either 0 or 1');
        }
        Topic::syncAll($publish);
        return 0;
    }

    public function actionTopics()
    {
        $this->_client = new Client();
        $local = \yii\helpers\ArrayHelper::map(Topic::find()->asArray()->all(), 'id', 'name');
        foreach ($batch as $id => $topic) {
            Topic::sync($topic);
            unset($local[$id]);
        }
        Topic::deleteAll(['in', 'id', array_keys($local)]);
        return true;
    }

    private function _requestTopicBatch()
    {
        $topicFilterPrefix = isset($this->module->params['topicFilterPrefix']) ? $this->module->params['topicFilterPrefix'] : null;
        $autoscoopSuffix = isset($this->module->params['autoscoopSuffix']) ? $this->module->params['autoscoopSuffix'] : null;
        $batch = [];

        foreach ($this->_client->getTopics(TRUE) as $topic) {
            $id = $topic['id'];
            $name = $topic['name'];
            if (isset($topicFilterPrefix) ? strpos($name, $topicFilterPrefix) === 0 : true && isset($autoscoopSuffix) ? strpos($name, $autoscoopSuffix) !== strlen($name) - strlen($autoscoopSuffix) : true) {
                $batch[$id] = $topic;
                //echo $name . "\n";
            }
        }
        return $batch;
    }

    private function _initTopicSync()
    {
        
    }

    /*

      public function actionLink($topic, $route)
      {
      $model = Topic::resolve($topic);
      if (!isset($model)) {
      return false;
      }
      $data = ['topic_id' => $model->id, 'name' => $route];


      }

      public function actionUnlink($topic, $route)
      {
      $model = Topic::resolve($topic);
      if (!isset($model)) {
      return false;
      }
      $data = ['topic_id' => $model->id, 'name' => $route];
      }

      /*

      public function actionKeywords($topicId)
      {
      $model = Topic::findOne($topicId);
      if (!isset($model)) {
      throw new \Exception('Topic ID does not correspond to a database-entry');
      }
      $client = new Client();
      $keywordData = $client->getTopicKeywords($topicId);

      foreach ($keywordData as $keyword) {
      $this->_syncKeyword($keyword);
      $model->linkKeyword($keyword);
      }
      return 0;
      }

      private function _syncTopic($topicData, $publish)
      {
      $model = $this->_getLocalTopic($topicData);
      if (!isset($model)) {
      $model = $this->_createLocalTopic($topicData, $publish);
      }
      if (isset($this->module->params['mapTopic']) && is_callable($this->module->params['mapTopic'])) {
      call_user_func($this->module->params['mapTopic'], $model);
      }
      }

      private function _getLocalTopic($topicData)
      {
      $model = \humanized\scoopit\models\Topic::findOne($topicData['id']);
      if (!isset($model)) {
      $model = $this->_mergeLocalTopic($topicData);
      }
      return $model;
      }

      private function _mergeLocalTopic($topicData)
      {
      $model = \humanized\scoopit\models\Topic::findOne(['name' => $topicData['name']]);
      if (isset($model)) {
      $model->id = $topicData['id'];
      $model->save();
      }
      return $model;
      }

      private function _createLocalTopic($topicData, $publish)
      {
      $model = new Topic(['id' => $topicData['id'], 'name' => $topicData['name'], 'is_published' => $publish]);
      if (!$model->save()) {
      //VarDumper::dump($model->errors);
      return null;
      }
      return $model;
      }

      public function _syncKeyword($keyword)
      {
      $model = Keyword::findOne(['name' => $keyword]);
      //Create a new model if required
      if (!isset($model)) {
      $this->stdout('creating keyword with name ' . $keyword . "\n");
      $model = new Keyword(['name' => $keyword]);
      if (!$model->save()) {
      VarDumper::dump($model->errors);
      }
      //Sync names if scoop.it name has changed (existing model only)
      }
      return true;
      }
     * 
     */
}
