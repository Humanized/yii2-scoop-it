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
class SourceController extends Controller
{

    public function actionIndex($lastUpdate)
    {

        $client = new Client();
        $topics = $client->getTopics(TRUE);
        foreach ($topics as $topic) {
            $this->actionImportTopic($topic['id']);
        }

        return 0;
    }

    public function actionImportTopic($topicId)
    {
        $client = new Client();
        //  $sources = $client->getSources($topicId);
        //  foreach ($sources as $source) {
        //  }
        $scoops = $client->getScoops($topicId);
        foreach ($scoops as $scoop) {
            $this->_import($scoop, TRUE);
        }
        return 0;
    }

    private function _import($item, $withScoop = FALSE)
    {
        $model = Source::findOne($item->id);
        if (!isset($model)) {
            $model = new Source();
            $model->setPostAttributes($item);
            try {
                if ($model->save()) {
                    $this->stdout('New Scoop.it Meta Imported' . "\n");
                }
            } catch (\Exception $ex) {
                
            }
        }

        if (isset($model)) {
            $this->_importTags($item);
            if ($withScoop) {
                $this->_importScoop($item);
            }
        }
    }

    private function _importTags($item)
    {
        $scoop = Scoop::findOne($item->id);
        foreach ($item->tags as $tag) {
            $model = Tag::findOne(['name' => $tag]);
            if (!isset($model)) {
                $model = new Tag(['name' => $tag]);
                $model->save();
            }
        }
    }

    private function _importScoop($item)
    {
        $model = Scoop::findOne($item->id);
        if (!isset($model)) {
            $model = new Scoop();
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

}
