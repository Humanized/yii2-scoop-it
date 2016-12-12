<?php

namespace humanized\scoopit\models;

use Yii;
use humanized\scoopit\models\Tag;
use humanized\scoopit\models\Scoop;

/**
 * This is the model class for table "scoopit_scoop_tag".
 *
 * @property integer $scoop_id
 * @property integer $tag_id
 *
 * @property ScoopitScoop $scoop
 * @property ScoopitTag $tag
 */
class ScoopTag extends \yii\db\ActiveRecord
{

    public $postProcessor = null;
    public $postProcessing = false;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'scoopit_scoop_tag';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['scoop_id', 'tag_id'], 'required'],
            [['scoop_id', 'tag_id'], 'integer'],
            [['tag_id', 'scoop_id'], 'unique', 'targetAttribute' => ['tag_id', 'scoop_id']],
            [['scoop_id'], 'exist', 'skipOnError' => true, 'targetClass' => Scoop::className(), 'targetAttribute' => ['scoop_id' => 'id']],
            [['tag_id'], 'exist', 'skipOnError' => true, 'targetClass' => Tag::className(), 'targetAttribute' => ['tag_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'scoop_id' => 'Scoop ID',
            'tag_id' => 'Tag ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScoop()
    {
        return $this->hasOne(Scoop::className(), ['id' => 'scoop_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTag()
    {
        return $this->hasOne(Tag::className(), ['id' => 'tag_id']);
    }

    /**
     * Synchronise tags for a given scoop
     * 
     * @param array<mixed> $data Remote post object 
     * @param type $fn 
     * @return Scoop
     */
    public static function sync($data, $fn = null)
    {
        //get local tags
        $scoop = Scoop::findOne($data->id);

        $local = self::findOne($data->id);
        if (!isset($local)) {
            $local = new Scoop();
            $local->setPostAttributes($data);
        }
        //Attach post-processor $fn variable is provided
        if (isset($fn) && is_callable($fn)) {
            $local->postProcessor = $fn;
        }
        $local->save();
        return $local;
    }

    public function afterSave($insert, $changedAttributes)
    {

        if (isset($this->postProcessor) && !$this->postProcessing) {
            $postprocess = $this->postProcessor;
            $postprocess($this, $insert, $changedAttributes);
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    public function delete()
    {
        $tag = $this->tag;
        if (parent::delete()) {
            if (count(ScoopTag::findAll(['tag_id' => $tag->id])) == 0) {
                return $tag->delete();
            }
            return true;
        }
        return false;
    }

}
