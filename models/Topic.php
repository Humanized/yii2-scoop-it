<?php

namespace humanized\scoopit\models;

use Yii;
use humanized\scoopit\Client;

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
 * @property TopicMap $topicMap
 * @property TopicTag[] $topicTags
 * @property Tag[] $tags
 */
class Topic extends \yii\db\ActiveRecord
{

    public $excludedNewsItems = [];
    public $limit = null;

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
    public function getSourceTopics()
    {
        return $this->hasMany(SourceTopic::className(), ['topic_id' => 'id'])->andFilterWhere(['NOT IN', 'scoopit_source_topic.source_id', $this->excludedNewsItems]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSources()
    {
        $_q = $this->hasMany(Source::className(), ['id' => 'source_id'])->via('sourceTopics');
        if (isset($this->limit)) {
            $_q->limit($this->limit);
        }
        return $_q;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScoops()
    {
        $_q = $this->hasMany(Scoop::className(), ['id' => 'source_id'])->via('sourceTopics');
        if (isset($this->limit)) {
            $_q->limit($this->limit);
        }
        return $_q;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRelatedNews()
    {
        $sourcify = function($scoop) {
            return $scoop->source;
        };
        $this->limit = 3;
        return array_map($sourcify, $this->scoops);
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
    public function getTopicMap()
    {
        return $this->hasOne(TopicMap::className(), ['topic_id' => 'id']);
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

    /**
     * Returns a topic model by it's unique id or it's unique name
     * 
     * @param integer|string $mixed - Numeric topic-id or the topic-name 
     * @return Topic - the corresponding topic model
     */
    public static function resolve($mixed)
    {
        return Topic::findOne(!is_numeric($mixed) ? ['name' => $mixed] : $mixed);
    }

    public static function map($mixed, $value)
    {
        $model = self::resolve($mixed);
        //return false if topic not found
        if (!isset($model)) {
            return false;
        }
    }

    /**
     * 
     * @param type $publish
     * @return boolean
     */
    public static function syncAll($publish)
    {
        $local = \yii\helpers\ArrayHelper::map(Topic::find()->asArray()->all(), 'id', 'name');
        $client = new Client();
        $remote = $client->getTopics(TRUE);
        foreach ($remote as $data) {
            self::sync($data, $publish);
            unset($local[$data['id']]);
        }
        //Cleanup redundant local topics if required
        if (count($local) != 0) {
            return Topic::deleteAll(['in', 'id', $local]);
        }
        return true;
    }

    public static function sync($data)
    {
        $model = self::findOne($data['id']);

        //Register a remote topic-name change
        if (isset($model) && $model->name != $data['name']) {
            $model->name = $data['name'];
        }
        //Create local topic
        if (!isset($model)) {
            $model = new Topic(['id' => $data['id'], 'name' => $data['name']]);
        }
        $model->save();
        return $model;
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
