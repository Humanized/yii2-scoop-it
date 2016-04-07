<?php

namespace humanized\scoopit\models;

use Yii;

/**
 * This is the model class for table "scoopit_topic".
 *
 * @property integer $id
 * @property integer $is_published
 * @property string $name
 *
 * @property ScoopTopic[] $scoopTopics
 * @property Scoop[] $scoops
 * @property TopicKeyword[] $topicKeywords
 * @property Keyword[] $keywords
 * @property TopicMap[] $topicMaps
 * @property TopicTag[] $topicTags
 * @property Tag[] $tags
 */
class Topic extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'scoopit_topic';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['is_published'], 'integer'],
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
            'is_published' => 'Is Published',
            'name' => 'Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScoopTopics()
    {
        return $this->hasMany(ScoopTopic::className(), ['topic_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScoops()
    {
        return $this->hasMany(Scoop::className(), ['id' => 'scoop_id'])->viaTable('scoopit_scoop_topic', ['topic_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTopicKeywords()
    {
        return $this->hasMany(TopicKeyword::className(), ['topic_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeywords()
    {
        return $this->hasMany(Keyword::className(), ['id' => 'keyword_id'])->viaTable('scoopit_topic_keyword', ['topic_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTopicMaps()
    {
        return $this->hasMany(TopicMap::className(), ['topic_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTopicTags()
    {
        return $this->hasMany(TopicTag::className(), ['topic_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->viaTable('scoopit_topic_tag', ['topic_id' => 'id']);
    }

    public function linkKeyword($keyword)
    {

        $keywordId = $keyword;
        if (!is_numeric($keyword)) {
            $model = Keyword::findOne(['name' => $keyword]);
            if (!isset($model)) {
                return false;
            }
            $keywordId = $model->id;
        }
        $model = new TopicKeyword(['topic_id' => $this->id, 'keyword_id' => $keywordId]);
        try {
            if ($model->save()) {
                if (php_sapi_name() == "cli") {
                    echo 'New Topic linked to Keyword' . "\n";
                }
            }
        } catch (\Exception $ex) {
            
        }

        return true;
    }

}