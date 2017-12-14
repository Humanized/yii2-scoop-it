<?php

namespace humanized\scoopit\models\gui;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use humanized\scoopit\models\Scoop;
use yii\db\Query;

/**
 * ScoopSearch represents the model behind the search form about `humanized\scoopit\models\Scoop`.
 */
class ScoopSearch extends Scoop
{

    public $pageSize = 1;
    public $topicId = NULL;
    public $extraSafeAttributes = [];
    public $afterInitCallback;
    public $title;
    public $keywords = [];
    public $pub_range_start = NULL;
    public $pub_range_stop = NULL;

    /**
     *
     * @var Query
     */
    protected $query;
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
            //       [['date_published'], 'default', 'value' => NULL],
            [['pub_range_start', 'pub_range_stop'], 'date'],
            [array_merge(['title', 'date_published', 'pub_range_start', 'pub_range_stop', 'keywords'], $this->extraSafeAttributes), 'safe'],
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
        $this->buildSearchQuery();
        $this->applyContextFilters();
        $dataProvider = new ActiveDataProvider([
            'query' => $this->query,
            'pagination' => ['pageSize' => $this->pageSize]
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $this->applySearchFilters();
        $this->query->orderBy('date_retrieved DESC');
        return $dataProvider;
    }

    protected function buildSearchQuery()
    {
        //Join with source keywords and scoop tags
        $this->query = Scoop::find()->groupBy('scoopit_scoop.id')
                ->joinWith('tags')
                ->joinWith('source')
                ->joinWith('source.keywords')
        ;
    }

    protected function applyContextFilters()
    {
        if (isset($this->topicId)) {
            $this->query->joinWith('source.topics');
            $this->query->andWhere(['scoopit_source_topic.topic_id' => $this->topicId]);
        }
    }

    protected function applySearchFilters()
    {
        $this->_applyDateRangeFilters();
        $this->applyKeywordFilters();
        $this->query->andFilterWhere(['OR', ['LIKE', 'scoopit_source.title', $this->title], ['LIKE', 'scoopit_source.description_raw', $this->title]]);
    }

    private function _applyDateRangeFilters()
    {
        if (!isset($this->date_published) || $this->date_published == '') {

            return;
        }
        var_dump($this->date_published);
        $pos = strpos($this->date_published, ' to ');
        $this->pub_range_start = date('U', strtotime(substr($this->date_published, 0, $pos) . ' 0:00:00'));
        $this->pub_range_stop = date('U', strtotime(substr($this->date_published, $pos + 4) . ' 23:59:00'));
        $this->query->andFilterWhere(['BETWEEN', 'date_published', $this->pub_range_start, $this->pub_range_stop]);
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
        $this->query->andFilterWhere($filter);
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
