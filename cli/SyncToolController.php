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
class AdminController extends Controller
{

    public function actionGetTopics()
    {
        /**
         * GuzzleHttp\Client;
         */
        $client = new Client();
    }

    public function actionLoadTopics()
    {
        
    }

}
