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
use humanized\scoopit\models\Keyword;

/**
 * A CLI port of the Yii2 RBAC Manager Interface.
 *
 * 
 * @name RBAC Manager CLI
 * @version 0.1
 * @author Jeffrey Geyssens <jeffrey@humanized.be>
 * @package yii2-rbac
 *
 */
class TopicController extends Controller
{

    public function actionIndex($publish = 0)
    {
        if ($publish != 1 && $publish != 0) {
            throw new \InvalidArgumentException('provided publish parameter must be either 0 or 1');
        }
        /**
         * GuzzleHttp\Client;
         */
        $client = new Client();
        $topicData = $client->getTopics(TRUE);
        $this->stdout('found ' . count($topicData) . ' topics' . "\n");
        foreach ($topicData as $topic) {
            $this->_syncTopic($topic);

            $this->actionKeywords($topic['id']);
        }
    }

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

    private function _syncTopic($topic)
    {
        $model = Topic::findOne($topic['id']);
        //Create a new model if required
        if (!isset($model)) {
            $this->stdout('creating topic #' . $topic['id'] . ' with name ' . $topic['name'] . "\n");
            $model = new Topic(['id' => $topic['id'], 'name' => $topic['name'], 'is_published' => $publish]);
            if (!$model->save()) {
                VarDumper::dump($model->errors);
                return false;
            }
            //Sync names if scoop.it name has changed (existing model only)
        } elseif ($model->name != $topic['name']) {
            $this->stdout('changing short name #' . $topic['id'] . ': ' . $model->name . ' to ' . $topic['name'] . "\n");
            $model->name = $topic['name'];
            return $model->save();
        }
        return true;
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

}
