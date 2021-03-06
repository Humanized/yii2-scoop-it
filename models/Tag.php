<?php

namespace humanized\scoopit\models;

use Yii;

/**
 * This is the model class for table "scoopit_tag".
 *
 * @property integer $id
 * @property string $name
 *
 * @property TopicTag[] topicTags
 * @property Topic[] $topics
 */
class Tag extends \yii\db\ActiveRecord
{

    public $excludedNewsItems = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'scoopit_tag';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
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
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTopicTags()
    {
        return $this->hasMany(TopicTag::className(), ['tag_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTopics()
    {
        return $this->hasMany(Topic::className(), ['id' => 'topic_id'])->viaTable('scoopit_topic_tag', ['tag_id' => 'id']);
    }

    public static function sync($value)
    {
        $model = self::resolve(strtolower($value));
        if (!isset($model)) {
            $model = new Tag(['name' => strtolower($value)]);
            $model->save();
        }
        return $model;
    }

    /**
     * Returns a topic model by it's unique id or it's unique name
     * 
     * @param integer|string $mixed - Numeric topic-id or the topic-name 
     * @return Topic - the corresponding topic model
     */
    public static function resolve($mixed)
    {
        return self::findOne(!is_numeric($mixed) ? ['name' => $mixed] : $mixed);
    }

    public function getRelatedNews()
    {
        return [];
    }

}
