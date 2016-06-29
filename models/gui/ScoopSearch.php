<?php

namespace humanized\scoopit\models\gui;

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
    public $pub_range_start = NULL;
    public $pub_range_stop = NULL;
    private $_query;
    private $_keywordTables = [
        't' => ['scoopit_scoop_tag', 'tag_id'],
        'k' => ['scoopit_source_keyword', 'keyword_id']
    ];
    private $_keywordFilters = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['pub_range_start', 'pub_range_stop'], 'date'],
            [['title', 'pub_range_start', 'pub_range_stop', 'keywords'], 'safe'],
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

        $this->_initQuery();
        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $this->_query,
        ]);
        $dataProvider->pagination->pageSize = 3;
        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        $this->_applyFilters();
        $this->_query->orderBy('date_published DESC');
        return $dataProvider;
    }

    private function _initQuery()
    {
        //Join with source keywords and scoop tags
        $this->_query = Scoop::find()->groupBy('scoopit_scoop.id')
                ->joinWith('tags')
                ->joinWith('source')
                ->joinWith('source.keywords')
        ;

        if (isset($this->topicId)) {
            $this->_query->joinWith('source.topics');
            $this->_query->andWhere(['scoopit_source_topic.topic_id' => $this->topicId]);
        }
    }

    private function _applyFilters()
    {
        $this->_applyDateRangeFilters();
        $this->applyKeywordFilters();

        $this->_query->andFilterWhere(['LIKE', 'scoopit_source.title', $this->title]);
        // grid filtering conditions
        $this->_query->andFilterWhere([
            'date_published' => $this->date_published,
        ]);
    }

    private function _applyDateRangeFilters()
    {
        if (isset($this->pub_range_start)) {
            $this->pub_range_start = date('U', strtotime($this->pub_range_start . ' 0:00:00'));
        }

        if (isset($this->pub_range_stop)) {
            $this->pub_range_stop = date('U', strtotime($this->pub_range_stop . ' 23:59:00'));
        }
        $this->_query->andFilterWhere(['BETWEEN', 'date_published', $this->pub_range_start, $this->pub_range_stop]);
        // $this->_query->andFilterWhere(['BETWEEN', 'date_published', '2016-06-29', '2016-06-29']);

        if (isset($this->pub_range_start)) {
            $this->pub_range_start = date('d F Y', $this->pub_range_start);
        }
        if (isset($this->pub_range_stop)) {
            $this->pub_range_stop = date('d F Y', $this->pub_range_stop);
        }
    }

    protected function applyKeywordFilters()
    {
        //Do nothing when empty
        if (empty($this->keywords)) {
            return;
        }
        //Else process keywords through single character prefix

        foreach ($this->keywords as $keyword) {
            //Setup keywordFilters array
            $this->processKeyword($keyword);
        }



        $filter = null;

        if (count($this->_keywordFilters) > 1) {
            $filter = ['OR'];
        }

        foreach ($this->_keywordFilters as $prefix => $keywords) {
            $tableData = $this->_keywordTables[$prefix];
            $condition = ['IN', $tableData[0] . '.' . $tableData[1], $keywords];
            if (is_array($filter)) {
                $filter[] = $condition;
            }
            if (!is_array($filter)) {
                $filter = $condition;
            }
        }
        $this->_query->andFilterWhere($filter);
    }

    protected function processKeyword($keyword)
    {
        $prefix = substr($keyword, 0, 1);
        $id = substr($keyword, 1);
        if (!isset($this->_keywordFilters[$prefix])) {
            $this->_keywordFilters[$prefix] = [];
        }
        $this->_keywordFilters[$prefix][] = $id;
    }

}
