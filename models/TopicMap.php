<?php

namespace humanized\scoopit\models;

use Yii;

/**
 * This is the model class for table "scoopit_topic_map".
 *
 * @property integer $id
 * @property string $name
 * @property integer $topic_id
 *
 * @property Topic $topic
 */
class TopicMap extends \yii\db\ActiveRecord
{

    public $saveSuggestions = false;
    private $_client = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'scoopit_topic_map';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['topic_id'], 'required'],
            [['topic_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
            [['topic_id'], 'exist', 'skipOnError' => true, 'targetClass' => Topic::className(), 'targetAttribute' => ['topic_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'topic_id' => 'Topic ID',
        ];
    }

    public function sync()
    {
        
    }

}
