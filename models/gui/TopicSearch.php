<?php

namespace humanized\scoopit\models\gui;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use humanized\scoopit\models\Topic;
use humanized\scoopit\Client;

/**
 * TopicSearch represents the model behind the search form about `humanized\scoopit\models\Topic`.
 */
class TopicSearch extends Topic
{

    public $autoscoopSuffix = '-pool';
    public $topicFilterPrefix = 'nano-';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'position'], 'integer'],
            [['label'], 'safe'],
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
        $client = new Client();
        $client->autoscoopSuffix = '-pool';
        $client->topicFilterPrefix = 'nano-';
        $client->initAvailableTopics();

        $remote = $client->availableTopics;
        $local = Topic::find()->all();
        foreach ($local as $topic) {
            if (isset($remote[$topic->id])) {
                $remote[$topic->id]['label'] = $topic->label;
            }
        }

        $query = Topic::find();


        $models = $remote;

        // add conditions that should always apply here

        $dataProvider = new ArrayDataProvider([
            'allModels' => $models,
            'sort' => [
                'attributes' => ['id', 'name', 'position'],
            ],
            'pagination' => [
                'pageSize' => 100,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'position' => $this->position,
        ]);

        $query->andFilterWhere(['like', 'label', $this->label]);

        return $dataProvider;
    }

}
