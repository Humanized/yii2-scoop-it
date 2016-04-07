<?php

namespace humanized\scoopit\models;

use Yii;

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
            [['scoop_id'], 'exist', 'skipOnError' => true, 'targetClass' => ScoopitScoop::className(), 'targetAttribute' => ['scoop_id' => 'id']],
            [['tag_id'], 'exist', 'skipOnError' => true, 'targetClass' => ScoopitTag::className(), 'targetAttribute' => ['tag_id' => 'id']],
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
        return $this->hasOne(ScoopitScoop::className(), ['id' => 'scoop_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTag()
    {
        return $this->hasOne(ScoopitTag::className(), ['id' => 'tag_id']);
    }

}
