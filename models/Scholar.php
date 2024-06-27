<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\Pagination;

use app\models\ScholarIndicators;
use app\models\Orcid;
use app\models\User;

use yii\helpers\ArrayHelper;

class Scholar extends Model
{

    public $researcher;
    public $dois;
    public $found_ids_dois;
    public $indicators;
    public $missing_papers;

    public function __construct($researcher){
        parent::__construct();
        $this->researcher = $researcher;
    }

    public function fetchWorks() {

        // get scholar's works from ORCiD
        $orcid_works = Orcid::get_works($this->researcher->orcid, $this->researcher->access_token);

        // get dois from works (filter null or our empty dois)
        $this->dois = array_map(function($w) { return (isset($w["doi"])) ? $w["doi"] : null; }, $orcid_works);

        // get dois in db
        $this->found_ids_dois = (new \yii\db\Query())->select("internal_id, doi")->from('pmc_paper')->where(['doi' => $this->dois])->all();
        $found_dois = array_map(function ($r) { return ['doi' => $r['doi']]; }, $this->found_ids_dois);

        // find missing papers, comparing $works received from orcid with papers found in our database
        $this->missing_papers = array_udiff($orcid_works, $found_dois, function($a, $b) {
            if (!isset($a["doi"]))
                return -1;
            elseif (!isset($b["doi"]))
                return 1;
            else
                return strcmp($a["doi"], $b["doi"]);
        });

    }

    public function fetchCvNarrativeDois($cv_narrative_ids){

        $found_ids = array_column($this->found_ids_dois, 'internal_id');
        $matching_ids = array_intersect($found_ids, $cv_narrative_ids);

        $array_tmp = [];
        foreach ($matching_ids as $id) {
            $key = array_search($id, $found_ids);
            $array_tmp[] = $this->found_ids_dois[$key]['doi'];
        }
        $this->dois = $array_tmp;
    }

    public function getOnlyAllArticlesInPage() {

        $found_dois = array_column($this->found_ids_dois, 'doi');

        // fetch all papers in current page
        $papers = (new \yii\db\Query())
            ->select('internal_id, doi, year, title, journal')
            ->from('pmc_paper')
            ->where(['doi' => $found_dois])
            ->all();

        return $papers;
    }

    public function getArticlesInPage($topics, $tags, $roles, $accesses, $types, $sort_field) {

        $orderByClause = [];
        $impact_fields = Yii::$app->params['impact_fields'];

        $field = $impact_fields[$sort_field];
        $orderByClause[$field] = SORT_DESC;

        // build base query, filter user and dois
        $base_query = (new \yii\db\Query())
            ->select('internal_id')
            ->from('pmc_paper')
            ->where(['doi' => $this->dois]);

        // add applied filters
        if (!empty($topics)) {
            $base_query->innerJoin('concepts_to_papers', 'pmc_paper.internal_id = concepts_to_papers.paper_id')
                ->andWhere(['concepts_to_papers.concept_id' => $topics]);
        }

        if (!empty($tags)) {
            $base_query->innerJoin('tags_to_papers', 'pmc_paper.internal_id = tags_to_papers.paper_id
            AND tags_to_papers.user_id = ' . $this->researcher->user_id)
                ->andWhere(['tags_to_papers.tag_id' => $tags]);
        }

        if (!empty($roles)) {
            $base_query->innerJoin('involvement_to_papers', 'pmc_paper.internal_id = involvement_to_papers.paper_id
            AND involvement_to_papers.user_id = ' . $this->researcher->user_id)
                ->andWhere(['involvement_to_papers.involvement' => $roles]);
        }

        if (!empty($accesses)) {
            $base_query->andWhere(['is_oa' => $accesses]);
        }

        if (!empty($types)) {
            $base_query->andWhere([ 'type' => $types ]);
        }

        // count unique papers in the result set
        $base_query->groupBy('internal_id');
        $papers_num = $base_query->count();

        // calculate impact data
        $select_clause = 'doi, is_oa, type, '
            . $impact_fields["popularity"] . ', '
            . $impact_fields["influence"] . ', '
            . $impact_fields["impulse"] . ', '
            . $impact_fields["citation_count"] . ', '
            . $impact_fields["year"];

        $impact_data = $base_query->select($select_clause)->all();
        
        // add impact classes
        $impact_data = SearchForm::get_impact_class($impact_data);

        // transform work types as needed by the component
        $work_types = array_flip(array_map(function(&$val) {
            return strtolower($val['name']);
        }, Yii::$app->params['work_types']));

        $this->indicators = new ScholarIndicators(
            Yii::$app->params['impact_fields'],
            array_keys(Yii::$app->params['impact_classes']),
            $work_types,
            $impact_data
        );

        // paginated query to retrieve all paper details
        $pagination = new Pagination([
            'pageSize' => 30,
            'totalCount' => $papers_num,
        ]);

        // fetch details (and order) for paper in current page
        $papers = (new \yii\db\Query())
            ->select('pmc_paper.*, notes_to_papers.notes, GROUP_CONCAT(tags.name ORDER BY tags_to_papers.timestamp ASC) AS tags')
            ->from('pmc_paper')
            ->leftJoin('tags_to_papers', 'pmc_paper.internal_id = tags_to_papers.paper_id
                AND tags_to_papers.user_id = ' . $this->researcher->user_id)
            ->leftJoin('tags', 'tags.id = tags_to_papers.tag_id')
            ->leftJoin('notes_to_papers', 'pmc_paper.internal_id = notes_to_papers.paper_id
                AND notes_to_papers.user_id = ' . $this->researcher->user_id)
            ->where(['internal_id' => $base_query->select('internal_id')])
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

    public function getTopicFacets($topics, $tags, $roles, $accesses, $types, $facet_field) {

        $topics_query = (new \yii\db\Query())
            ->select('concepts.id, concepts.display_name, COUNT(concepts.id) as count')
            ->from('pmc_paper')
            ->innerJoin('concepts_to_papers', 'pmc_paper.internal_id = concepts_to_papers.paper_id')
            ->innerJoin('concepts', 'concepts.id = concepts_to_papers.concept_id')
            ->where(['doi' => $this->dois]);

            if (!empty($topics) && strcmp($facet_field, "topic")) {
                $topics_query->andWhere([ 'concepts.id' => $topics]);
            }

            if (!empty($tags)) {
                $tags_subquery = (new \yii\db\Query())
                    ->select('internal_id')
                    ->from('pmc_paper')
                    ->innerJoin('tags_to_papers', 'pmc_paper.internal_id = tags_to_papers.paper_id
                            AND tags_to_papers.user_id = ' . $this->researcher->user_id)
                    ->innerJoin('tags', 'tags.id = tags_to_papers.tag_id')
                    ->where(['doi' => $this->dois])
                    ->andWhere(['tags.id' => $tags]);

                $topics_query->andWhere(['internal_id' => $tags_subquery]);
            }

            if (!empty($roles)) {
                $roles_subquery = (new \yii\db\Query())
                    ->select('internal_id')
                    ->from('pmc_paper')
                    ->innerJoin('involvement_to_papers', 'pmc_paper.internal_id = involvement_to_papers.paper_id
                            AND involvement_to_papers.user_id = ' . $this->researcher->user_id)
                    ->where(['doi' => $this->dois])
                    ->andWhere(['involvement' => $roles]);

                $topics_query->andWhere(['internal_id' => $roles_subquery]);
            }

            if (!empty($accesses)) {
                $topics_query->andWhere(['is_oa' => $accesses]);
            }

            if (!empty($types)) {
                $topics_query->andWhere(['type' => $types]);
            }

            return $topics_query->groupBy('concepts.id')->orderBy('count DESC')->all();
    }

    public function getTagFacets($topics, $tags, $roles, $accesses, $types, $facet_field) {

        $tags_query = (new \yii\db\Query())
            ->select('tags.id, tags.name, COUNT(tags.id) as count')
            ->from('pmc_paper')
            ->innerJoin('tags_to_papers', 'pmc_paper.internal_id = tags_to_papers.paper_id
                    AND tags_to_papers.user_id = ' . $this->researcher->user_id)
            ->innerJoin('tags', 'tags.id = tags_to_papers.tag_id')
            ->where(['doi' => $this->dois]);

            if (!empty($tags) && strcmp($facet_field, "tag")) {
                $tags_query->andWhere([ 'tags.id' => $tags]);
            }

            if (!empty($topics)) {
                $topics_subquery = (new \yii\db\Query())
                    ->select('internal_id')
                    ->from('pmc_paper')
                    ->innerJoin('concepts_to_papers', 'pmc_paper.internal_id = concepts_to_papers.paper_id')
                    ->innerJoin('concepts', 'concepts.id = concepts_to_papers.concept_id')
                    ->where(['doi' => $this->dois])
                    ->andWhere([ 'concepts.id' => $topics]);

                $tags_query->andWhere(['internal_id' => $topics_subquery]);
            }

            if (!empty($roles)) {
                $roles_subquery = (new \yii\db\Query())
                    ->select('internal_id')
                    ->from('pmc_paper')
                    ->innerJoin('involvement_to_papers', 'pmc_paper.internal_id = involvement_to_papers.paper_id
                            AND involvement_to_papers.user_id = ' . $this->researcher->user_id)
                    ->where(['doi' => $this->dois])
                    ->andWhere(['involvement' => $roles]);

                $tags_query->andWhere(['internal_id' => $roles_subquery]);
            }

            if (!empty($accesses)) {
                $tags_query->andWhere(['is_oa' => $accesses]);
            }

            if (!empty($types)) {
                $tags_query->andWhere(['type' => $types]);
            }

            return $tags_query->groupBy('tags.id')->orderBy('count DESC')->all();
    }

    public function getRoleFacets($topics, $tags, $roles, $accesses, $types, $facet_field) {

        $roles_query = (new \yii\db\Query())
            ->select('involvement, COUNT(involvement) as count')
            ->from('pmc_paper')
            ->innerJoin('involvement_to_papers', 'pmc_paper.internal_id = involvement_to_papers.paper_id
                    AND involvement_to_papers.user_id = ' . $this->researcher->user_id)
            ->where(['doi' => $this->dois]);

        if (!empty($roles) && strcmp($facet_field, "role")) {
            $roles_query->andWhere(['involvement' => $roles]);
        }

        if (!empty($topics)) {
            $topics_subquery = (new \yii\db\Query())
                ->select('internal_id')
                ->from('pmc_paper')
                ->innerJoin('concepts_to_papers', 'pmc_paper.internal_id = concepts_to_papers.paper_id')
                ->innerJoin('concepts', 'concepts.id = concepts_to_papers.concept_id')
                ->where(['doi' => $this->dois])
                ->andWhere([ 'concepts.id' => $topics]);

            $roles_query->andWhere(['internal_id' => $topics_subquery]);
        }

        if (!empty($tags)) {
            $tags_subquery = (new \yii\db\Query())
                ->select('internal_id')
                ->from('pmc_paper')
                ->innerJoin('tags_to_papers', 'pmc_paper.internal_id = tags_to_papers.paper_id
                        AND tags_to_papers.user_id = ' . $this->researcher->user_id)
                ->innerJoin('tags', 'tags.id = tags_to_papers.tag_id')
                ->where(['doi' => $this->dois])
                ->andWhere(['tags.id' => $tags]);

            $roles_query->andWhere(['internal_id' => $tags_subquery]);
        }

        if (!empty($accesses)) {
            $roles_query->andWhere(['is_oa' => $accesses]);
        }

        if (!empty($types)) {
            $roles_query->andWhere(['type' => $types]);
        }

        return $roles_query->groupBy('involvement')->orderBy('count DESC')->all();
    }

    public function getAccessFacets($topics, $tags, $roles, $accesses, $types, $facet_field) {

        $accesses_query = (new \yii\db\Query())
            ->select('is_oa, COUNT(*) as count')
            ->from('pmc_paper')
            ->where(['doi' => $this->dois]);

        if (!empty($accesses) && strcmp($facet_field, "access")) {
            $accesses_query->andWhere(['is_oa' => $accesses]);
        }

        if (!empty($topics)) {
            $topics_subquery = (new \yii\db\Query())
                ->select('internal_id')
                ->from('pmc_paper')
                ->innerJoin('concepts_to_papers', 'pmc_paper.internal_id = concepts_to_papers.paper_id')
                ->innerJoin('concepts', 'concepts.id = concepts_to_papers.concept_id')
                ->where(['doi' => $this->dois])
                ->andWhere([ 'concepts.id' => $topics]);

            $accesses_query->andWhere(['internal_id' => $topics_subquery]);
        }

        if (!empty($tags)) {
            $tags_subquery = (new \yii\db\Query())
                ->select('internal_id')
                ->from('pmc_paper')
                ->innerJoin('tags_to_papers', 'pmc_paper.internal_id = tags_to_papers.paper_id
                        AND tags_to_papers.user_id = ' . $this->researcher->user_id)
                ->innerJoin('tags', 'tags.id = tags_to_papers.tag_id')
                ->where(['doi' => $this->dois])
                ->andWhere(['tags.id' => $tags]);

            $accesses_query->andWhere(['internal_id' => $tags_subquery]);
        }

        if (!empty($roles)) {
            $roles_subquery = (new \yii\db\Query())
                ->select('internal_id')
                ->from('pmc_paper')
                ->innerJoin('involvement_to_papers', 'pmc_paper.internal_id = involvement_to_papers.paper_id
                        AND involvement_to_papers.user_id = ' . $this->researcher->user_id)
                ->where(['doi' => $this->dois])
                ->andWhere(['involvement' => $roles]);

            $accesses_query->andWhere(['internal_id' => $roles_subquery]);
        }

        if (!empty($types)) {
            $accesses_query->andWhere(['type' => $types]);
        }

        return $accesses_query->groupBy('is_oa')->orderBy('count DESC')->all();
    }

    public function getTypeFacets($topics, $tags, $roles, $accesses, $types, $facet_field) {

        $types_query = (new \yii\db\Query())
            ->select('type, COUNT(*) as count')
            ->from('pmc_paper')
            ->where(['doi' => $this->dois]);

        if (!empty($types) && strcmp($facet_field, "type")) {
            $types_query->andWhere(['type' => $types]);
        }

        if (!empty($accesses)) {
            $types_query->andWhere(['is_oa' => $accesses]);
        }

        if (!empty($topics)) {
            $topics_subquery = (new \yii\db\Query())
                ->select('internal_id')
                ->from('pmc_paper')
                ->innerJoin('concepts_to_papers', 'pmc_paper.internal_id = concepts_to_papers.paper_id')
                ->innerJoin('concepts', 'concepts.id = concepts_to_papers.concept_id')
                ->where(['doi' => $this->dois])
                ->andWhere([ 'concepts.id' => $topics]);

            $types_query->andWhere(['internal_id' => $topics_subquery]);
        }

        if (!empty($tags)) {
            $tags_subquery = (new \yii\db\Query())
                ->select('internal_id')
                ->from('pmc_paper')
                ->innerJoin('tags_to_papers', 'pmc_paper.internal_id = tags_to_papers.paper_id
                        AND tags_to_papers.user_id = ' . $this->researcher->user_id)
                ->innerJoin('tags', 'tags.id = tags_to_papers.tag_id')
                ->where(['doi' => $this->dois])
                ->andWhere(['tags.id' => $tags]);

            $types_query->andWhere(['internal_id' => $tags_subquery]);
        }

        if (!empty($roles)) {
            $roles_subquery = (new \yii\db\Query())
                ->select('internal_id')
                ->from('pmc_paper')
                ->innerJoin('involvement_to_papers', 'pmc_paper.internal_id = involvement_to_papers.paper_id
                        AND involvement_to_papers.user_id = ' . $this->researcher->user_id)
                ->where(['doi' => $this->dois])
                ->andWhere(['involvement' => $roles]);

            $types_query->andWhere(['internal_id' => $roles_subquery]);
        }

        return $types_query->groupBy('type')->orderBy('count DESC')->all();
    }

    public function getFacets($topics, $tags, $roles, $accesses, $types, $facet_field) {

        $topic_facets = $this->getTopicFacets($topics, $tags, $roles, $accesses, $types, $facet_field);
        $tag_facets = $this->getTagFacets($topics, $tags, $roles, $accesses, $types, $facet_field);
        $role_facets = $this->getRoleFacets($topics, $tags, $roles, $accesses, $types, $facet_field);
        $access_facets = $this->getAccessFacets($topics, $tags, $roles, $accesses, $types, $facet_field);
        $type_facets = $this->getTypeFacets($topics, $tags, $roles, $accesses, $types, $facet_field);

        return [
            'topics' => [
                'options' => ArrayHelper::map($topic_facets, 'id', 'display_name'),
                'counts' => ArrayHelper::map($topic_facets, 'id', 'count'),
            ],
            'tags' => [
                'options' => ArrayHelper::map($tag_facets, 'id', 'name'),
                'counts' => ArrayHelper::map($tag_facets, 'id', 'count'),
            ],
            'roles' => [
                'options' => array_map(function($var) { return Yii::$app->params['involvement_fields'][$var]; }, ArrayHelper::map($role_facets, 'involvement', 'involvement')),
                'counts' => ArrayHelper::map($role_facets, 'involvement', 'count'),
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