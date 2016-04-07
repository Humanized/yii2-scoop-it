<?php

namespace humanized\scoopit\models;

use Yii;
use humanized\scoopit\models\Topic;
use humanized\scoopit\models\Keyword;
/**
 * This is the model class for table "scoopit_topic_keyword".
 *
 * @property integer $topic_id
 * @property integer $keyword_id
 *
 * @property Keyword $keyword
 * @property Topic $topic
 */
class TopicKeyword extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'scoopit_topic_keyword';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['topic_id', 'keyword_id'], 'required'],
            [['topic_id', 'keyword_id'], 'integer'],
            [['keyword_id'], 'exist', 'skipOnError' => true, 'targetClass' => Keyword::className(), 'targetAttribute' => ['keyword_id' => 'id']],
            [['topic_id'], 'exist', 'skipOnError' => true, 'targetClass' => Topic::className(), 'targetAttribute' => ['topic_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'topic_id' => 'Topic ID',
            'keyword_id' => 'Keyword ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeyword()
    {
        return $this->hasOne(Keyword::className(), ['id' => 'keyword_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTopic()
    {
        return $this->hasOne(Topic::className(), ['id' => 'topic_id']);
    }

}
