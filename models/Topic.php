<?php

namespace humanized\scoopit\models;

use Yii;

/**
 * This is the model class for table "scoopit_topic".
 *
 * @property integer $id
 * @property string $label
 * @property integer $position
 *
 * @property ScoopitSourceTopic[] $scoopitSourceTopics
 * @property ScoopitSource[] $sources
 * @property ScoopitTopicKeyword[] $scoopitTopicKeywords
 * @property ScoopitKeyword[] $keywords
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

    protected $gridColumns = ['default' => ['name', 'label', 'position'],];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'position'], 'integer'],
            [['label'], 'string', 'max' => 255],
            [['id'], 'unique'],
            [['label'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'label' => 'Label',
            'position' => 'Position',
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
    public function getScoopitSourceTopics()
    {
        return $this->hasMany(ScoopitSourceTopic::className(), ['topic_id' => 'id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScoopitTopicKeywords()
    {
        return $this->hasMany(ScoopitTopicKeyword::className(), ['topic_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeywords()
    {
        return $this->hasMany(ScoopitKeyword::className(), ['id' => 'keyword_id'])->viaTable('scoopit_topic_keyword', ['topic_id' => 'id']);
    }




}
