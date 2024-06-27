<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\Pagination;

use app\models\Orcid;

use yii\helpers\ArrayHelper;

class Readings extends Model
{

    public $user;
    public $indicators;
    public $missing_papers;

    public function __construct($user){
        parent::__construct();
        $this->user = $user;
      }

    public function get($topics, $tags, $rd_status, $accesses, $types, $sort_field) {
        $orderByClause = [];
        $impact_fields = Yii::$app->params['impact_fields'];

        $field = $impact_fields[$sort_field];
        $orderByClause[$field] = SORT_DESC;

        // build base query, filter user and showit = true
        $base_query = (new \yii\db\Query())
            ->select('users_likes.paper_id')
            ->from('users_likes')
            ->where(['users_likes.user_id' => $this->user->id])
            ->andWhere(['users_likes.showit' => true]);

        // add applied filters
        if (!empty($topics)) {
            $base_query->innerJoin('concepts_to_papers', 'concepts_to_papers.paper_id = users_likes.paper_id')
                ->andWhere(['concepts_to_papers.concept_id' => $topics]);
        }

        if (!empty($tags)) {
            $base_query->innerJoin('tags_to_papers', 'users_likes.paper_id = tags_to_papers.paper_id
            AND tags_to_papers.user_id = ' . $this->user->id)
                ->andWhere(['tags_to_papers.tag_id' => $tags]);
        }

        if (!empty($rd_status)) {
            $base_query->andWhere(['users_likes.reading_status' => $rd_status]);
        }

        if (!empty($accesses) || !empty($types)) {
            $base_query->innerJoin('pmc_paper', 'pmc_paper.internal_id = users_likes.paper_id
                AND users_likes.user_id = ' . $this->user->id);

            if (!empty($accesses)) {
                $base_query->andWhere(['pmc_paper.is_oa' => $accesses]);
            }

            if (!empty($types)) {
                $base_query->andWhere(['pmc_paper.type' => $types]);
            }
        }

        // count unique papers in the result set
        $base_query->groupBy('paper_id');
        $papers_num = $base_query->count();

        // paginated query to retrieve all paper details
        $pagination = new Pagination([
            'pageSize' => 30,
            'totalCount' => $papers_num,
        ]);

        // fetch details (and order) for paper in current page
        $papers = (new \yii\db\Query())
            ->select('pmc_paper.*, notes_to_papers.notes, users_likes.reading_status, GROUP_CONCAT(tags.name ORDER BY tags_to_papers.timestamp ASC) AS tags')
            ->from('users_likes')
            ->innerJoin('pmc_paper', 'pmc_paper.internal_id = users_likes.paper_id')
            ->leftJoin('tags_to_papers', 'pmc_paper.internal_id = tags_to_papers.paper_id
                AND tags_to_papers.user_id = ' . $this->user->id)
            ->leftJoin('tags', 'tags.id = tags_to_papers.tag_id')
            ->leftJoin('notes_to_papers', 'pmc_paper.internal_id = notes_to_papers.paper_id
                AND notes_to_papers.user_id = ' . $this->user->id)
            ->where(['internal_id' => $base_query->select('users_likes.paper_id')])
            ->andWhere([ 'users_likes.showit' => true ])
            ->andWhere([ 'users_likes.user_id' => $this->user->id])
            ->groupBy('internal_id')
            ->orderBy($orderByClause)
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        // get impact scores
        $papers = SearchForm::get_impact_class($papers);
        // get concepts and scores
        $papers = Concepts::getConcepts($papers, 'internal_id');
        // get impact scores per concept
        $papers = SearchForm::get_concepts_impact_class($papers);
    
        return [
            'pagination' => $pagination,
            'papers' => $papers,
            'papers_num' => $papers_num,
        ];
    }

    public function getTopicFacets($topics, $tags, $rd_status, $accesses,  $types, $facet_field) {

        $topics_query = (new \yii\db\Query())
            ->select('concepts.id, concepts.display_name, COUNT(concepts.id) as count')
            ->from('users_likes')
            ->innerJoin('concepts_to_papers', 'users_likes.paper_id = concepts_to_papers.paper_id')
            ->innerJoin('concepts', 'concepts.id = concepts_to_papers.concept_id')
            ->where(['users_likes.user_id' => $this->user->id])
            ->andWhere(['users_likes.showit' => true]);

            if (!empty($topics) && strcmp($facet_field, "topic")) {
                $topics_query->andWhere([ 'concepts.id' => $topics]);
            }

            if (!empty($rd_status)) {
                $topics_query->andWhere([ 'reading_status' => $rd_status ]);
            }

            if (!empty($tags)) {
                $tags_subquery = (new \yii\db\Query())
                    ->select('users_likes.paper_id')
                    ->from('users_likes')
                    ->innerJoin('tags_to_papers', 'users_likes.paper_id = tags_to_papers.paper_id
                            AND tags_to_papers.user_id = ' . $this->user->id)
                    ->innerJoin('tags', 'tags.id = tags_to_papers.tag_id')
                    ->where(['users_likes.user_id' => $this->user->id])
                    ->andWhere(['users_likes.showit' => true])
                    ->andWhere(['tags.id' => $tags]);

                $topics_query->andWhere(['users_likes.paper_id' => $tags_subquery]);
            }

            if (!empty($accesses) || !empty($types)) {
                $topics_query->innerJoin('pmc_paper', 'pmc_paper.internal_id = users_likes.paper_id
                    AND users_likes.user_id = ' . $this->user->id);

                if (!empty($accesses)) {
                    $topics_query->andWhere(['pmc_paper.is_oa' => $accesses]);
                }

                if (!empty($types)) {
                    $topics_query->andWhere(['pmc_paper.type' => $types]);
                }
            }

            return $topics_query->groupBy('concepts.id')->orderBy('count DESC')->all();
    }

    public function getTagFacets($topics, $tags, $rd_status, $accesses,  $types, $facet_field) {

        $tags_query = (new \yii\db\Query())
            ->select('tags.id, tags.name, COUNT(tags.id) as count')
            ->from('users_likes')
            ->innerJoin('tags_to_papers', 'users_likes.paper_id = tags_to_papers.paper_id
                    AND tags_to_papers.user_id = ' . $this->user->id)
            ->innerJoin('tags', 'tags.id = tags_to_papers.tag_id')
            ->where(['users_likes.user_id' => $this->user->id])
            ->andWhere(['users_likes.showit' => true]);

            if (!empty($tags) && strcmp($facet_field, "tag")) {
                $tags_query->andWhere([ 'tags.id' => $tags]);
            }

            if (!empty($rd_status)) {
                $tags_query->andWhere([ 'reading_status' => $rd_status ]);
            }

            if (!empty($topics)) {
                $topics_subquery = (new \yii\db\Query())
                    ->select('users_likes.paper_id')
                    ->from('users_likes')
                    ->innerJoin('concepts_to_papers', 'users_likes.paper_id = concepts_to_papers.paper_id')
                    ->innerJoin('concepts', 'concepts.id = concepts_to_papers.concept_id')
                    ->where(['users_likes.user_id' => $this->user->id])
                    ->andWhere(['users_likes.showit' => true])
                    ->andWhere([ 'concepts.id' => $topics]);

                $tags_query->andWhere(['users_likes.paper_id' => $topics_subquery]);
            }

            if (!empty($accesses) || !empty($types)) {
                $tags_query->innerJoin('pmc_paper', 'pmc_paper.internal_id = users_likes.paper_id
                    AND users_likes.user_id = ' . $this->user->id);

                if (!empty($accesses)) {
                    $tags_query->andWhere(['pmc_paper.is_oa' => $accesses]);
                }

                if (!empty($types)) {
                    $tags_query->andWhere(['pmc_paper.type' => $types]);
                }
            }

            return $tags_query->groupBy('tags.id')->orderBy('count DESC')->all();
    }

    public function getReadingStatusFacets($topics, $tags, $rd_status, $accesses, $types, $facet_field) {
        $rd_status_query = (new \yii\db\Query())
            ->select('reading_status, COUNT(reading_status) as count')
            ->from('users_likes')
            ->where(['users_likes.user_id' => $this->user->id])
            ->andWhere(['users_likes.showit' => true]);

        if (!empty($rd_status) && strcmp($facet_field, "rd_status")) {
            $rd_status_query->andWhere(['reading_status' => $rd_status]);
        }

        if (!empty($topics)) {
            $topics_subquery = (new \yii\db\Query())
                ->select('users_likes.paper_id')
                ->from('users_likes')
                ->innerJoin('concepts_to_papers', 'users_likes.paper_id = concepts_to_papers.paper_id')
                ->innerJoin('concepts', 'concepts.id = concepts_to_papers.concept_id')
                ->where(['users_likes.user_id' => $this->user->id])
                ->andWhere(['users_likes.showit' => true])
                ->andWhere([ 'concepts.id' => $topics]);

            $rd_status_query->andWhere(['users_likes.paper_id' => $topics_subquery]);
        }

        if (!empty($tags)) {
            $tags_subquery = (new \yii\db\Query())
                ->select('users_likes.paper_id')
                ->from('users_likes')
                ->innerJoin('tags_to_papers', 'users_likes.paper_id = tags_to_papers.paper_id
                        AND tags_to_papers.user_id = ' . $this->user->id)
                ->innerJoin('tags', 'tags.id = tags_to_papers.tag_id')
                ->where(['users_likes.user_id' => $this->user->id])
                ->andWhere(['users_likes.showit' => true])
                ->andWhere(['tags.id' => $tags]);

            $rd_status_query->andWhere(['users_likes.paper_id' => $tags_subquery]);
        }

        if (!empty($accesses) || !empty($types)) {
            $rd_status_query->innerJoin('pmc_paper', 'pmc_paper.internal_id = users_likes.paper_id
                AND users_likes.user_id = ' . $this->user->id);

            if (!empty($accesses)) {
                $rd_status_query->andWhere(['pmc_paper.is_oa' => $accesses]);
            }

            if (!empty($types)) {
                $rd_status_query->andWhere(['pmc_paper.type' => $types]);
            }

        }

        return $rd_status_query->groupBy('reading_status')->orderBy('count DESC')->all();
    }

    public function getAccessFacets($topics, $tags, $rd_status, $accesses, $types, $facet_field) {
        $accesses_query = (new \yii\db\Query())
            ->select('pmc_paper.is_oa, COUNT(*) as count')
            ->from('users_likes')
            ->innerJoin('pmc_paper', 'pmc_paper.internal_id = users_likes.paper_id
                AND users_likes.user_id = ' . $this->user->id)
            ->where(['users_likes.user_id' => $this->user->id])
            ->andWhere(['users_likes.showit' => true]);

        if (!empty($accesses) && strcmp($facet_field, "access")) {
            $accesses_query->andWhere(['pmc_paper.is_oa' => $accesses]);
        }

        if (!empty($types)) {
            $accesses_query->andWhere(['pmc_paper.type' => $types]);
        }

        if (!empty($rd_status)) {
            $accesses_query->andWhere(['reading_status' => $rd_status]);
        }

        if (!empty($topics)) {
            $topics_subquery = (new \yii\db\Query())
                ->select('users_likes.paper_id')
                ->from('users_likes')
                ->innerJoin('concepts_to_papers', 'users_likes.paper_id = concepts_to_papers.paper_id')
                ->innerJoin('concepts', 'concepts.id = concepts_to_papers.concept_id')
                ->where(['users_likes.user_id' => $this->user->id])
                ->andWhere(['users_likes.showit' => true])
                ->andWhere([ 'concepts.id' => $topics]);

            $accesses_query->andWhere(['users_likes.paper_id' => $topics_subquery]);
        }

        if (!empty($tags)) {
            $tags_subquery = (new \yii\db\Query())
                ->select('users_likes.paper_id')
                ->from('users_likes')
                ->innerJoin('tags_to_papers', 'users_likes.paper_id = tags_to_papers.paper_id
                        AND tags_to_papers.user_id = ' . $this->user->id)
                ->innerJoin('tags', 'tags.id = tags_to_papers.tag_id')
                ->where(['users_likes.user_id' => $this->user->id])
                ->andWhere(['users_likes.showit' => true])
                ->andWhere(['tags.id' => $tags]);

            $accesses_query->andWhere(['users_likes.paper_id' => $tags_subquery]);
        }

        return $accesses_query->groupBy('pmc_paper.is_oa')->orderBy('count DESC')->all();
    }

    public function getTypesFacets($topics, $tags, $rd_status, $accesses, $types, $facet_field) {
        $types_query = (new \yii\db\Query())
            ->select('pmc_paper.type, COUNT(*) as count')
            ->from('users_likes')
            ->innerJoin('pmc_paper', 'pmc_paper.internal_id = users_likes.paper_id
                AND users_likes.user_id = ' . $this->user->id)
            ->where(['users_likes.user_id' => $this->user->id])
            ->andWhere(['users_likes.showit' => true]);

        if (!empty($types) && strcmp($facet_field, "type")) {
            $types_query->andWhere(['pmc_paper.type' => $types]);
        }

        if (!empty($accesses)) {
            $types_query->andWhere(['pmc_paper.is_oa' => $accesses]);
        }

        if (!empty($rd_status)) {
            $types_query->andWhere(['reading_status' => $rd_status]);
        }

        if (!empty($topics)) {
            $topics_subquery = (new \yii\db\Query())
                ->select('users_likes.paper_id')
                ->from('users_likes')
                ->innerJoin('concepts_to_papers', 'users_likes.paper_id = concepts_to_papers.paper_id')
                ->innerJoin('concepts', 'concepts.id = concepts_to_papers.concept_id')
                ->where(['users_likes.user_id' => $this->user->id])
                ->andWhere(['users_likes.showit' => true])
                ->andWhere([ 'concepts.id' => $topics]);

            $types_query->andWhere(['users_likes.paper_id' => $topics_subquery]);
        }

        if (!empty($tags)) {
            $tags_subquery = (new \yii\db\Query())
                ->select('users_likes.paper_id')
                ->from('users_likes')
                ->innerJoin('tags_to_papers', 'users_likes.paper_id = tags_to_papers.paper_id
                        AND tags_to_papers.user_id = ' . $this->user->id)
                ->innerJoin('tags', 'tags.id = tags_to_papers.tag_id')
                ->where(['users_likes.user_id' => $this->user->id])
                ->andWhere(['users_likes.showit' => true])
                ->andWhere(['tags.id' => $tags]);

            $types_query->andWhere(['users_likes.paper_id' => $tags_subquery]);
        }

        return $types_query->groupBy('pmc_paper.type')->orderBy('count DESC')->all();
    }

    public function getFacets($topics, $tags, $rd_status, $accesses, $types, $facet_field) {

        $topic_facets = $this->getTopicFacets($topics, $tags, $rd_status, $accesses, $types, $facet_field);
        $tag_facets = $this->getTagFacets($topics, $tags, $rd_status, $accesses, $types, $facet_field);
        $rd_status_facets = $this->getReadingStatusFacets($topics, $tags, $rd_status, $accesses, $types, $facet_field);
        $access_facets = $this->getAccessFacets($topics, $tags, $rd_status, $accesses, $types, $facet_field);
        $type_facets = $this->getTypesFacets($topics, $tags, $rd_status, $accesses, $types, $facet_field);

        return [
            'topics' => [
                'options' => ArrayHelper::map($topic_facets, 'id', 'display_name'),
                'counts' => ArrayHelper::map($topic_facets, 'id', 'count'),
            ],
            'tags' => [
                'options' => ArrayHelper::map($tag_facets, 'id', 'name'),
                'counts' => ArrayHelper::map($tag_facets, 'id', 'count'),
            ],
            'rd_status' => [
                'options' => array_map(function($var) { return Yii::$app->params['reading_fields'][$var]; }, ArrayHelper::map($rd_status_facets, 'reading_status', 'reading_status')),
                'counts' => ArrayHelper::map($rd_status_facets, 'reading_status', 'count'),
            ],
            'accesses' => [
                'options' => array_map(function($var) { return Yii::$app->params['openness'][$var]; }, ArrayHelper::map($access_facets, 'is_oa', 'is_oa')),
                'counts' => ArrayHelper::map($access_facets, 'is_oa', 'count'),
            ],
            'types' => [
                'options' => array_map(function($var) { return Yii::$app->params['work_types'][$var]; }, ArrayHelper::map($type_facets, 'type', 'type')),
                'counts' => ArrayHelper::map($type_facets, 'type', 'count'),
            ],
        ];
    }
}