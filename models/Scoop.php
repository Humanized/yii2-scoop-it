<?php

namespace humanized\scoopit\models;

use Yii;

/**
 * This is the model class for table "scoop".
 *
 * @property integer $id
 * @property integer $date_published
 *
 * @property Source $source
 * @property ScoopTopic[] $scoopitScoopTopics
 * @property Topic[] $topics
 */
class Scoop extends \yii\db\ActiveRecord
{

    public $postProcessor = null;
    public $tagPostProcessor = null;
    public $postProcessing = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'scoopit_scoop';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'date_published'], 'required'],
            [['date_published'], 'integer'],
            [['id'], 'exist', 'skipOnError' => true, 'targetClass' => Source::className(), 'targetAttribute' => ['id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date_published' => 'Date Published',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScoopTags()
    {
        return $this->hasMany(ScoopTag::className(), ['scoop_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->viaTable('scoopit_scoop_tag', ['scoop_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSource()
    {
        return $this->hasOne(Source::className(), ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScoopTopics()
    {
        return $this->hasMany(ScoopTopic::className(), ['scoop_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTopics()
    {
        return $this->hasMany(Topic::className(), ['id' => 'topic_id'])->viaTable('scoop_topic', ['scoop_id' => 'id']);
    }

    /**
     * Sets the current attribute with Scoop.it object attributes
     * 
     * @param stdClass $post Scoop.it post object 
     */
    public function setPostAttributes($post)
    {

        $attributes = [
            'id' => $post->id,
            'date_published' => substr($post->curationDate, 0, 10),
        ];
        $this->setAttributes($attributes);
    }

    public function linkTag($tag)
    {

        $tagId = $tag;
        if (!is_numeric($tag)) {
            $model = Tag::findOne(['name' => $tag]);
            if (!isset($model)) {
                return false;
            }
            $tagId = $model->id;
        }
        $model = new ScoopTag(['scoop_id' => $this->id, 'tag_id' => $tagId]);

        if (isset($this->tagPostProcessor)) {
            $model->postProcessor = $this->tagPostProcessor;
        }
        try {
            if ($model->save()) {
                if (php_sapi_name() == "cli") {
                    echo 'New Topic linked to Tag' . "\n";
                }
            }
        } catch (\Exception $ex) {
            
        }

        return true;
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
