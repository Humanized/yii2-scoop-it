<?php

namespace humanized\scoopit;

/**
 * 
 * @name Yii2 RBAC Module Class 
 * @version 0.1 
 * @author Jeffrey Geyssens <jeffrey@humanized.be>
 * @package yii2-rbac
 */
class ScoopIt extends \yii\base\Module
{

    public function init()
    {
        parent::init();
        if (\Yii::$app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'humanized\scoopit\cli';
        }
    
    }

}
