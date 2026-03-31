<?php

namespace app\models;

use akiraz2\blog\traits\IActiveStatus;
use yii\db\Expression;
use yii\data\ActiveDataProvider;

/**
 * Search model for {@see BlogPost} (no category join).
 */
class BlogPostSearch extends BlogPost {
    const SCENARIO_ADMIN = 'admin';

    const SCENARIO_USER = 'user';

    public $tag;

    public function rules() {
        return [
            [['id', 'click', 'user_id', 'status'], 'integer'],
            [['title', 'slug', 'tag'], 'string'],
            [['content'], 'string'],
        ];
    }

    public function scenarios() {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_ADMIN] = ['id', 'click', 'user_id', 'status', 'title', 'slug', 'content', 'created_at', 'updated_at'];
        $scenarios[self::SCENARIO_USER] = ['title', 'tag'];

        return $scenarios;
    }

    public function search($params) {
        $query = BlogPost::find();
        $query->orderBy(['created_at' => SORT_DESC]);

        if ($this->scenario == self::SCENARIO_USER) {
            $query->andWhere(['{{%blog_post}}.status' => IActiveStatus::STATUS_ACTIVE]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this->module->blogPostPageCount,
            ],
        ]);

        if (! ($this->load($params, ($this->scenario == self::SCENARIO_USER) ? '' : 'BlogPostSearch') && $this->validate())) {
            return $dataProvider;
        }

        if ($this->scenario == self::SCENARIO_ADMIN) {
            $query->andFilterWhere([
                'id' => $this->id,
                'status' => $this->status,
                'click' => $this->click,
                'user_id' => $this->user_id,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]);
            $query->andFilterWhere(['like', 'title', $this->title])
                ->andFilterWhere(['like', 'content', $this->content])
                ->andFilterWhere(['like', 'slug', $this->slug]);
        } elseif ($this->scenario == self::SCENARIO_USER && trim((string) $this->tag) !== '') {
            $query->andWhere(new Expression(
                "FIND_IN_SET(:tag, REPLACE({{%blog_post}}.tags, ' ', '')) > 0",
                [':tag' => trim((string) $this->tag)]
            ));
        }

        return $dataProvider;
    }
}
