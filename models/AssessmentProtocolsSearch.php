<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AssessmentProtocols;

/**
 * AssessmentProtocolsSearch represents the model behind the search form of `app\models\AssessmentProtocols`.
 */
class AssessmentProtocolsSearch extends AssessmentProtocols
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'assessment_framework_id'], 'integer'],
            [['name', 'scope'], 'safe'],
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
        $query = AssessmentProtocols::find();

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
            'assessment_framework_id' => $this->assessment_framework_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'scope', $this->scope]);

        return $dataProvider;
    }
}