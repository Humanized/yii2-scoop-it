<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace humanized\scoopit\cli;

use yii\console\Controller;
use humanized\scoopit\Client;
use humanized\scoopit\models\Topic;

/**
 * Provides an interface to clean both local and remote scoop.it topic content
 *
 * 
 * @name Scoop.it CLI Clean Tool
 * @version 0.1
 * @author Jeffrey Geyssens <jeffrey@humanized.be>
 * @package yii2-scoop.it
 *
 */
class CleanController extends Controller
{

    /**
     * 
     * 
     * @param integer|string $topicId
     */
    public function actionLocal($topicId, $removeTopic = false)
    {
        $topic = \humanized\scoopit\models\Topic::findOne(!is_numeric($topicId) ? ['name' => $topicId] : $topicId);
        if (NULL === $topic) {
            $this->stdout("No Such Topic \n");
            return 1;
        }
        foreach ($topic->sources as $source) {
            $source->delete();
        }
        if ($removeTopic) {
            $topic->delete();
        }
    }

    /**
     * 
     * @param integer|string $topicId
     */
    public function actionRemote($topicId)
    {
        $topic = \humanized\scoopit\models\Topic::findOne(!is_numeric($topicId) ? ['name' => $topicId] : $topicId);
        if (NULL === $topic) {
            $this->stdout("No Such Topic \n");
            return 1;
        }
        $queryParams = ['action' => 'delete'];
        $client = new Client();
        foreach ($client->getScoops($topic->id) as $remoteScoop) {
            $queryParams['id'] = $remoteScoop->id;
            $client->post('api/1/post', ['query' => $queryParams]);
        }
    }

    public function actionIndex($removeTopic = 0)
    {
        $topicData = Topic::find()->all();
        foreach ($topicData as $topic) {
            $this->actionLocal($topic->id, $removeTopic);
        }
        return 0;
    }

}
