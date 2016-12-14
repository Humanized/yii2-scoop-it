<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace humanized\scoopit\cli;

use yii\console\Controller;
use yii\helpers\Console;
use humanized\scoopit\Client;
use humanized\scoopit\models\Topic;
use humanized\scoopit\models\Source;
use humanized\scoopit\models\SourceTopic;

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
     * @return int
     */
    public function actionOrphans()
    {
        $orphans = Source::find()->leftJoin('scoopit_source_topic', 'id=source_id')->where(['IS', 'source_id', NULL])->all();
        echo count($orphans) . "\n";
        foreach ($orphans as $orphan) {
            $orphan->delete();
        }
        return 0;
    }

    public function actionRemoteRemoved()
    {
        $client = new Client();
        $topics = $this->_client->getTopics(TRUE);
        foreach ($topics as $topic) {
            $posts = $client->curatedPosts($topic->id, 10);
            foreach ($posts as $post) {
                if (in_array('!rm', $post->tags)) {
                    $client->deleteScoop($post->id);
                }
            }
        }
        return 0;
    }

    /**
     * 
     * 
     * @param integer|string $topicId
     */
    public function actionAllLocal($topicId, $removeTopic = false)
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
        return 0;
    }

    /**
     * 
     * @param integer|string $topicId
     */
    public function actionAllRemote($topicId)
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
        return 0;
    }

    /**
     * 
     * @param type $removeTopic
     * @return int
     */
    public function actionAll($removeTopic = 0)
    {
        $topicData = Topic::find()->all();
        foreach ($topicData as $topic) {
            $this->actionLocal($topic->id, $removeTopic);
        }
        return 0;
    }

}
