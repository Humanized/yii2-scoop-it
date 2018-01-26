<?php

namespace humanized\scoopit\models;

use Yii;

/**
 * This is the model class for table "source".
 *
 * @property integer $id
 * @property string $url
 * @property string $title
 * @property string $description_raw
 * @property string $description_html
 * @property integer $date_retrieved
 * @property string $image_source
 * @property integer $image_height
 * @property integer $image_width
 * @property string $image_small
 * @property string $image_medium
 * @property string $image_large
 * @property string $language_id
 *
 * @property Scoop $scoop
 * @property SourceKeyword[] $sourceKeywords
 * @property Keyword[] $keywords
 */
class Source extends \yii\db\ActiveRecord
{

    public $topicPostProcessor = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'scoopit_source';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url', 'date_retrieved'], 'required'],
            [['date_retrieved', 'image_height', 'image_width'], 'integer'],
            [['description_raw', 'description_html'], 'string'],
            [['image_source', 'image_small', 'image_medium', 'image_large'], 'string', 'max' => 2083],
            [['url'], 'string', 'max' => 1000],
            [['title'], 'string', 'max' => 400],
            [['language_id'], 'string', 'max' => 2],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => 'Url',
            'title' => 'Title',
            'description_raw' => 'Description Raw',
            'description_html' => 'Description Html',
            'date_retrieved' => 'Date Retrieved',
            'image_source' => 'Image Source',
            'image_height' => 'Image Height',
            'image_width' => 'Image Width',
            'image_small' => 'Image Small',
            'image_medium' => 'Image Medium',
            'image_large' => 'Image Large',
            'language_id' => 'Language ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScoop()
    {
        return $this->hasOne(Scoop::className(), ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSourceTopics()
    {
        return $this->hasMany(SourceTopic::className(), ['source_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTopics()
    {
        return $this->hasMany(Topic::className(), ['id' => 'topic_id'])->viaTable('scoopit_source_topic', ['source_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSourceKeywords()
    {
        return $this->hasMany(SourceKeyword::className(), ['source_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeywords()
    {
        return $this->hasMany(Keyword::className(), ['id' => 'keyword_id'])->viaTable('scoopit_source_keyword', ['source_id' => 'id']);
    }

    /**
     * Sets the current attribute with Scoop.it object attributes
     * 
     * @param stdClass $post Scoop.it post object 
     */
    public function setPostAttributes($post)
    {

        $attributes = [
          //  'id' => $post->id,
            'url' => $post->url,
            'title' => $post->title,
            'description_raw' => $post->content,
            'description_html' => $post->htmlContent,
            'date_retrieved' => substr($post->publicationDate, 0, 10),
            //Image attributes for various sizes
            'image_source' => isset($post->imageUrl) ? $post->imageUrl : NULL,
            'image_width' => isset($post->imageWidth) ? $post->imageWidth : NULL,
            'image_height' => isset($post->imageHeight) ? $post->imageHeight : NULL,
            'image_small' => isset($post->smallImageUrl) ? $post->smallImageUrl : NULL,
            'image_medium' => isset($post->mediumImageUrl) ? $post->mediumImageUrl : NULL,
            'image_large' => isset($post->largeImageUrl) ? $post->largeImageUrl : NULL,
            //Attributes to improve
            'publisher_id' => NULL,
            'country_id' => 'us',
            'language_id' => 'EN', //Can be got from topic
        ];
        $this->setAttributes($attributes);
    }

    public function linkTopic($mixed)
    {

        $topic = Topic::findOne($mixed);
        if (!isset($topic)) {
            return false;
        }

        return SourceTopic::sync($topic->id, $this->id, $this->topicPostProcessor);
    }

    /**
     * 
     * @param type $topicId
     * @return boolean
     */
    public function hasTopic($mixed)
    {
        var_dump($mixed);
        $topic = Topic::resolve($mixed);
        $model = SourceTopic::find()->where(['topic_id' => $topic->id, 'source_id' => $this->id])->one();
        return isset($model);
    }

    /**
     * 
     * @param type $item
     */
    public static function resolve($post)
    {
        return self::find()->filterWhere(['url' => $post->url])->one();
    }

    /**
     * 
     * @param type $item
     * @return \humanized\scoopit\models\Source
     */
    public static function create($item)
    {

        $model = new Source();
        $model->setPostAttributes($item);

        try {
            if ($model->save()) {
                return $model;
            }
        } catch (\Exception $ex) {
            var_dump($model->errors);
        }
        return null;
    }

    public static function importPost($post, $postprocessorClass)
    {
        //Get local copy of suggestion (using it's id or url)
        $local = self::resolve($post);
        //Create it if it does not yet exit
        if (!isset($local)) {
            $local = self::create($post);
        }
        //Setup postprocessor after linking the local source to topic 

        if (isset($postprocessorClass) && method_exists($postprocessorClass, 'afterCurableSynchronised')) {
            $local->topicPostProcessor = [$postprocessorClass, 'afterCurableSynchronised'];
        }

        //Link Suggestion to topic and force remote flag
        $local->linkTopic($post->topicId);

        if (isset($postprocessorClass) && method_exists($postprocessorClass, 'afterCurableSynchronised')) {
            call_user_func([$postprocessorClass, 'afterCurableSynchronised'], Topic::findOne($post->topicId), $local);
        }
        return $local;
    }

}
