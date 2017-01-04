<?php

namespace humanized\scoopit\models;

use humanized\scoopit\models\Tag;

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

    /**
     * 
     * @param type $tags
     */
    public function syncTags($tags)
    {

        $local = \yii\helpers\ArrayHelper::map($this->tags, 'name', 'id');
        //import remote tags
        foreach ($tags as $value) {
            //   echo 'remote:' . $value;
            $tag = Tag::sync(strtolower($value));
            $this->linkTag($tag->id);
            if (isset($local[strtolower($value)])) {
                unset($local[strtolower($value)]);
            }
        }
        //cleanup local tags
        $this->unlinkTags(array_values($local));
    }

    /**
     * 
     * @param type $id id of tag to link to scoop
     * @return type
     */
    public function linkTag($id)
    {
        $model = new ScoopTag(['scoop_id' => $this->id, 'tag_id' => $id]);
        if (isset($this->tagPostProcessor)) {
            $model->postProcessor = $this->tagPostProcessor;
        }
        return $model->save();
    }

    public static function synchronisePost($post, $postprocessorClass)
    {
        //create-or-retrieve local record storing suggestion meta-data
        $source = Source::importPost($post, $postprocessorClass);

        if (!isset($source)) {
//            $this->stderr('Unhandled Exception: Source could not be created or retrieved');
            return null;
        }
        //create-or-retrieve updated local record storing publication meta-data and tags
        $scoop = self::sync($post, $postprocessorClass);
        // $scoop = self::sync($post, self::getPostprocessor($postprocessorClass, 'afterScoop'), self::getPostprocessor($postprocessorClass, 'afterScoopTag'));
        if (!isset($scoop)) {
            //          $this->stderr('Unhandled Exception: Scoop could not be created or retrieved');
            return null;
        }

        if (isset($postprocessorClass) && method_exists($postprocessorClass, 'afterCuratedSynchronised')) {
            call_user_func([$postprocessorClass, 'afterCuratedSynchronised'], Topic::findOne($post->topicId), $scoop);
        }
        return $scoop;
    }

    /**
     * 
     * @param type $data
     * @param type $afterScoopFn
     * @param type $afterTagFn
     * @return type
     */
    public static function sync($data, $postprocessorClass)
    {
        //Synchronise local scoop record
        $local = self::_syncScoop($data, $postprocessorClass);
        //Prepare for tag postprocessing
        if (isset($postprocessorClass) && method_exists($postprocessorClass, 'afterScoopTag')) {
            $local->tagPostProcessor = [$postprocessorClass, 'afterScoopTag'];
        }
        //Synchronise tags
        $local->syncTags($data->tags);
        return $local;
    }

    private static function _syncScoop($data, $postprocessorClass)
    {
        $local = self::findOne($data->id);
        if (!isset($local)) {
            $local = new Scoop();
            $local->setPostAttributes($data);
        }
        if (isset($postprocessorClass) && method_exists($postprocessorClass, 'afterScoop')) {
            $local->postProcessor = [$postprocessorClass, 'afterScoop'];
        }
        $local->save();
        //   echo 'saved scoop' . $local->id;
        return $local;
    }

    private function unlinkTags($ids)
    {
        foreach ($ids as $id) {
            $this->unlinkTag($id);
        }
    }

    public function unlinkTag($id)
    {
        return ScoopTag::find()->where(['scoop_id' => $this->id, 'tag_id' => $id])->one()->delete();
    }

    public function afterSave($insert, $changedAttributes)
    {
        //Run post-processor when set and not already postprocessing
        if (isset($this->postProcessor) && !$this->postProcessing) {
            $this->postProcessing = true;
            $postprocess = $this->postProcessor;
            $postprocess($this, $insert, $changedAttributes);
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    public function remote()
    {
        $client = new \humanized\scoopit\Client();
        return $client->getPost($this->id);
    }

}
