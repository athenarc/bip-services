<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Elements;
use yii\db\Query;

/**
 * ElementsSearch represents the model behind the search form of `app\models\Elements`.
 */
class ElementsSearch extends Elements
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'template_id', 'order'], 'integer'],
            [['name', 'type'], 'safe'],
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
        $query = Elements::find();

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
            'template_id' => $this->template_id,
            'order' => $this->order,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'type', $this->type]);

        return $dataProvider;
    }

    public static function findElementsUsers($template_id)
    {
        $query1 = (new Query())
            ->select(['element_id', 'template_id', 'total_users' => 'COUNT(*)'])
            ->from(ElementTableInstances::tableName())
            ->where(['template_id' => $template_id])
            ->groupBy(['element_id']);

        $query2 = (new Query())
            ->select(['element_id', 'template_id', 'total_users' => 'COUNT(*)'])
            ->from(ElementDropdownInstances::tableName())
            ->where(['template_id' => $template_id])
            ->groupBy(['element_id']);

        $query3 = (new Query())
            ->select(['element_id', 'template_id', 'total_users' => 'COUNT(*)'])
            ->from(ElementNarrativeInstances::tableName())
            ->where(['template_id' => $template_id])
            ->groupBy(['element_id']);

        $query4 = (new Query())
            ->select(['element_id', 'template_id', 'total_users' => 'COUNT(*)'])
            ->from([
                (new Query())
                    ->select(['user_id', 'template_id', 'element_id'])
                    ->from(ElementBulletedListItem::tableName())
                    ->where(['template_id' => $template_id])
                    ->groupBy(['user_id', 'element_id'])
            ])
            ->groupBy(['element_id']);

        // Combine queries using UNION ALL
        $finalQuery = (new Query())
            ->from(['combined' => $query1->union($query2)->union($query3)->union($query4)])
            ->where(['template_id' => $template_id]); // Final filtering

        // Execute query
        return $finalQuery->all();
    }
}
