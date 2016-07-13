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
use yii\helpers\Console;

/**
 * A CLI port of the Yii2 RBAC Manager Interface.
 *
 * 
 * @name Scoop.it connection test CLI 
 * @version 0.1
 * @author Jeffrey Geyssens <jeffrey@humanized.be>
 * @package yii2-scoopit
 *
 */
class TestController extends Controller
{

    private $_callbackUri;
    private $_params = [];
    private $_middlewareConfig = [];
    private $_authenticationVerifier;

    public function actionIndex()
    {
        /**
         * GuzzleHttp\Client;
         */
        $client = new Client();
        $ok = TRUE;
        $out = 'Connection Test Passed';
        try {
            /**
             * GuzzleHttp\Psr7\Response;
             */
            $response = $client->get('api/1/test');
            var_dump($response->getBody()->getContents());

            if ($response->getStatusCode() != 200) {
                $out = 'Connection Test Failed with code:' . $response->getStatusCode();
                $ok = FALSE;
            }
        } catch (\Exception $ex) {
            $out = 'Connection Test Failed with message: ' . "\n" . $ex->getMessage();
            $ok = FALSE;
        }
        $this->stdout($out . "\n");
        return 0;
    }

    public function actionDebugAutoScoop($topicId)
    {
        $topic = Topic::findOne(!is_numeric($topicId) ? ['name' => $topicId] : $topicId);
        if (NULL === $topic) {
            $this->stdout("No Such Topic \n");
            return 1;
        }
        $client = new Client();

        //Auto scoop when condition is satisfied

        if (isset($this->module->params['autoScoopCondition']) && call_user_func($this->module->params['autoScoopCondition'], $topic)) {
            $this->stdout('auto-scooping: ');
            $this->stdout($topic->name."\n", Console::FG_GREEN, Console::BOLD);
        }
    }

}
