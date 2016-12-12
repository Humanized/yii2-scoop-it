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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTopic()
    {
        return $this->hasOne(Topic::className(), ['id' => 'topic_id']);
    }

    /**
     * Returns a topic-map model by it's topic-id or it's unique name
     * 
     * @param integer|string $mixed - Numeric topic-id or the topic-name 
     * @return Topic - the corresponding topic model
     */
    public static function resolve($mixed)
    {
        return self::findOne([(is_numeric($mixed) ? 'topic_id' : 'name') => $mixed]);
    }

    public static function sync($data)
    {
        
    }

    public static function updatePost()
    {
        
    }

}
