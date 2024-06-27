<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Indicators;

/**
 * IndicatorsSearch represents the model behind the search form of `app\models\Indicators`.
 */
class IndicatorsSearch extends Indicators
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'level', 'semantics', 'intuition', 'parameters', 'calculation', 'limitations', 'availability', 'code', 'references'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
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
        $query = (isset($params['sort'])) ? Indicators::find() : Indicators::find()->orderBy(['level' => SORT_ASC, 'semantics' => SORT_ASC]);

        // add conditions that should always apply here
        $this->load($params, '');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $dataProvider;
    }
}
