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
 * @property Scoop $Scoop
 * @property SourceKeyword[] $SourceKeywords
 * @property Keyword[] $keywords
 */
class Source extends \yii\db\ActiveRecord
{

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
            [['id', 'url', 'date_retrieved'], 'required'],
            [['date_retrieved', 'image_height', 'image_width'], 'integer'],
            [['description_raw', 'description_html'], 'string'],
            [['url', 'image_source', 'image_small', 'image_medium', 'image_large'], 'string', 'max' => 2083],
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
    public function getSourceKeywords()
    {
        return $this->hasMany(SourceKeyword::className(), ['source_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeywords()
    {
        return $this->hasMany(Keyword::className(), ['id' => 'keyword_id'])->viaTable('source_keyword', ['source_id' => 'id']);
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

    public function linkTopic($topicId)
    {
        $topic = Topic::findOne($topicId);
        if (!isset($topic)) {
            return false;
        }
        try {
            $model = new SourceTopic(['topic_id' => $topicId, 'source_id' => $this->id]);
            if ($model->save()) {
                if (php_sapi_name() == "cli") {
                    echo 'New Topic linked to Source' . "\n";
                }
            }
        } catch (\Exception $ex) {
            
        }
    }

}