<?php

namespace humanized\scoopit\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use humanized\scoopit\models\Scoop;

/**
 * ScoopSearch represents the model behind the search form about `humanized\scoopit\models\Scoop`.
 */
class ScoopSearch extends Scoop
{

    public $title;
    public $keywords = [];
    public $topicId = NULL;
    public $date_range_from;
    public $date_range_to;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['date_range_from','date_range_to'], 'date'],
            [['title', 'date_range_from', 'date_range_to', 'keywords'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Scoop::find()->joinWith('source');
        if (isset($this->topicId)) {
            $query->joinWith('source.topics');
            $query->andWhere(['scoopit_source_topic.topic_id' => $this->topicId]);
        }


        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->pagination->pageSize = 3;

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['LIKE', 'scoopit_source.title', $this->title]);

        // grid filtering conditions
        $query->andFilterWhere([
            'date_published' => $this->date_published,
        ]);

        $query->orderBy('date_published,date_retrieved');

        return $dataProvider;
    }

}
