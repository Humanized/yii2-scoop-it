<?php

namespace humanized\scoopit\models\gui;

use Yii;
use humanized\scoopit\models\Tag;
use humanized\scoopit\models\Keyword;
use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * This is the base class for the scoop.it gui SearchLabel model;
 * It provides 
 * 
 * By default, it considers 
 *
 * @property integer $id
 * @property string $name
 *
 */
class SearchLabel extends Model
{

    /**
     * 
     * 
     * 
     * @param array $config
     * @return type
     */
    public static function getSelectData(array $config = [])
    {
        $model = new self($config);
        $query = $model->_getTagQuery()->union($model->_getKeywordQuery());

        $custom = $model->_getCustomQuery();
        if (isset($custom)) {
            $query->union($custom);
        }
        return \yii\helpers\ArrayHelper::map($query->asArray()->all(), 'id', 'name');
    }

    private function _getTagQuery()
    {
        return Tag::find()->select(['id' => 'CONCAT("t",id)', 'name']);
    }

    private function _getKeywordQuery()
    {
        return Keyword::find()->select(['id' => 'CONCAT("k",id)', 'name']);
    }

    protected function _getCustomQuery()
    {
        return NULL;
    }

}
