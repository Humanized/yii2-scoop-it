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

}
