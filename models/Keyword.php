<?php

namespace humanized\scoopit\models;

use Yii;

/**
 * This is the model class for table "scoopit_keyword".
 *
 * @property integer $id
 * @property string $name
 *
 * @property SourceKeyword[] $sourceKeywords
 * @property Source[] $sources
 * @property TopicKeyword[] $topicKeywords
 * @property Topic[] $topics
 */
class Keyword extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'scoopit_keyword';
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
    public function getSourceKeywords()
    {
        return $this->hasMany(SourceKeyword::className(), ['keyword_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSources()
    {
        return $this->hasMany(Source::className(), ['id' => 'source_id'])->viaTable('scoopit_source_keyword', ['keyword_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTopicKeywords()
    {
        return $this->hasMany(TopicKeyword::className(), ['keyword_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTopics()
    {
        return $this->hasMany(Topic::className(), ['id' => 'topic_id'])->viaTable('scoopit_topic_keyword', ['keyword_id' => 'id']);
    }

}
