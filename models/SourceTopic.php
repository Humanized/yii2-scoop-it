<?php

namespace humanized\scoopit\models;

use Yii;

/**
 * This is the model class for table "scoopit_source_topic".
 *
 * @property integer $source_id
 * @property integer $topic_id
 *
 * @property Source $source
 * @property Topic $topic
 */
class SourceTopic extends \yii\db\ActiveRecord
{

    public $postProcessor = null;
    public $postProcessing = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'scoopit_source_topic';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['source_id', 'topic_id'], 'required'],
            [['source_id', 'topic_id'], 'integer'],
            [['source_id', 'topic_id'], 'unique', 'targetAttribute' => ['source_id', 'topic_id']],
            [['source_id'], 'exist', 'skipOnError' => true, 'targetClass' => Source::className(), 'targetAttribute' => ['source_id' => 'id']],
            [['topic_id'], 'exist', 'skipOnError' => true, 'targetClass' => Topic::className(), 'targetAttribute' => ['topic_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'source_id' => 'Source ID',
            'topic_id' => 'Topic ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSource()
    {
        return $this->hasOne(Source::className(), ['id' => 'source_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTopic()
    {
        return $this->hasOne(Topic::className(), ['id' => 'topic_id']);
    }

    public static function sync($topicId, $sourceId, $afterTopicLinkFn = null)
    {
        $model = self::_syncModel($topicId, $sourceId, $afterTopicLinkFn);
        if (isset($model)) {
            return $model;
        }
        return false;
    }

    private static function _syncModel($topicId, $sourceId, $fn)
    {
        //Get/Create link between scoop and topic
        $data = ['topic_id' => $topicId, 'source_id' => $sourceId];
        $model = self::find()->where($data)->one();
        if (!isset($model)) {
            $model = new SourceTopic(array_merge($data, ["postProcessor" => $fn]));
            $model->save();
        }
        return $model;
    }

    public function afterSave($insert, $changedAttributes)
    {

        if (isset($this->postProcessor) && !$this->postProcessing) {
            $postprocess = $this->postProcessor;
            $postprocess($this, $insert, $changedAttributes);
        }

        return parent::afterSave($insert, $changedAttributes);
    }

}
