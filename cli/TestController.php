<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace humanized\scoopit\cli;

use yii\console\Controller;
use humanized\scoopit\Client;
use yii\helpers\Console;

/**
 * Provides an interface to clean both local and remote scoop.it topic content
 *
 * 
 * @name Scoop.it CLI Test Tool
 * @version 0.1
 * @author Jeffrey Geyssens <jeffrey@humanized.be>
 * @package yii2-scoop.it
 *
 */
class TestController extends Controller
{

    public function actionIndex()
    {
        /**
         * GuzzleHttp\Client;
         */
        $client = new Client();

        $out = 'Connection Test Passed: ';
        $authenticated = null;
        try {
            /**
             * GuzzleHttp\Psr7\Response;
             */
            $response = $client->get('api/1/test');
            if ($response->getStatusCode() != 200) {
                $out = 'Connection Test Failed with code:' . $response->getStatusCode();
            }
            if ($response->getStatusCode() == 200) {
                //Determine Mode
                $tmp = json_decode($response->getBody()->getContents());
                $authenticated = isset($tmp->connectedUser);
            }
        } catch (\Exception $ex) {
            $out = 'Connection Test Failed with message: ' . "\n" . $ex->getMessage();
        }

        $this->stdout($out);
        if (isset($authenticated)) {
            $this->stdout('IN ' . ($authenticated ? 'AUTHENTICATED' : 'ANONYMOUS' ) . " MODE\n", Console::FG_GREEN, Console::BOLD);
        }


        return 0;
    }

}
