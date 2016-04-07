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

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'date_published'], 'integer'],
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



        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        
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
            'date_published' => $this->date_published,
        ]);
        $query->orderBy('date_published,date_retrieved');

        return $dataProvider;
    }

}
