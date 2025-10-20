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

    public function fetchWorksLimited($sort_field, $top_k = null) {
        
        $orcid_works = Orcid::get_works($this->researcher->orcid, $this->researcher->access_token);

        $all_orcid_dois = array_filter(array_map(function($w) { return $w["doi"] ?? null; }, $orcid_works));


        // get dois in db
        $this->found_ids_dois = (new \yii\db\Query())
                            ->select('p.internal_id, pd.doi')
                            ->from('pmc_paper p')
                            ->innerJoin('pmc_paper_pids pd', 'p.internal_id = pd.paper_id')
                            ->where(['doi' => $all_orcid_dois])
                            ->all();

        // contains all dois, all versions of all papers.
        $found_dois = array_map(function ($r) { return ['doi' => $r['doi']]; }, $this->found_ids_dois);

        // contains one doi per paper
        $dois_query = (new \yii\db\Query())
            ->select('pmc_paper_pids.doi')
            ->from('pmc_paper')
            ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
            ->where(['doi' => $all_orcid_dois])
            ->groupBy('internal_id');

        // Apply ordering and limit if $top_k_config is set
        if ($top_k !== null) {
            $dois_query->orderBy([
            Yii::$app->params['impact_fields'][$sort_field] => SORT_DESC])
            ->limit($top_k);
        }

        $this->dois = array_column($dois_query->all(), 'doi');
           

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
            ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
            ->where(['doi' => $found_dois])
            ->groupBy('internal_id')
            ->all();

        return $papers;
    }

    public function getArticlesInPage($topics, $tags, $roles, $accesses, $types, $sort_field, $show_pagination_config, $page_size_config, $top_k = null, $page_param = 'page', $page_size_param = 'per-page') {
        Yii::debug("TOP_K received in getArticlesInPage(): " . var_export($top_k, true), __METHOD__);
        $impact_fields = Yii::$app->params['impact_fields'];
        $orderByClause = [
            $impact_fields[$sort_field] => SORT_DESC
        ];

        // build base query, filter user and dois
        $base_query = (new \yii\db\Query())
            ->select('internal_id')
            ->from('pmc_paper')
            ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
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

        // subquery to get internal_ids of papers in result set
        $ids_subquery = (new \yii\db\Query())
            ->from(['bq' => $base_query])
            ->select('internal_id');

        // fetch impact scores for papers in result set
        $select_clause = 'doi, is_oa, type, '
            . $impact_fields["popularity"] . ', '
            . $impact_fields["influence"] . ', '
            . $impact_fields["impulse"] . ', '
            . $impact_fields["citation_count"] . ', '
            . $impact_fields["year"];

        $impact_data = (new \yii\db\Query())
            ->select($select_clause)
            ->from('pmc_paper')
            ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
            ->where(['internal_id' => $ids_subquery])
            ->all();
        
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
        // if page_size_config is not set, default to one page
        // paginated query to retrieve all paper details
        $pagination = null;
        $offset = 0;

        if ($show_pagination_config) {

            $effective_page_size = ($page_size_config !== null)
                ? max(1, (int)$page_size_config)
                : $papers_num;

            $total_for_pagination = ($top_k !== null)
                ? min($papers_num, (int)$top_k)
                : $papers_num;

            $pagination = new Pagination([
                'pageSize'      => $effective_page_size,
                'totalCount'    => $total_for_pagination,
                'pageParam'     => $page_param,
                'pageSizeParam' => $page_size_param,
                'validatePage'  => true,
            ]);

            $cleanParams = Yii::$app->request->get();
            unset($cleanParams['page'], $cleanParams['per-page']);
            $pagination->params = $cleanParams;

            $offset = $pagination->offset;
            $limit  = $pagination->limit;
        } else {
            $offset = 0;
            $limit  = ($top_k !== null) ? (int)$top_k : null;
        }

        // fetch details (and order) for paper in current page
        $papers_query = (new \yii\db\Query())
            ->select('pmc_paper.*, pmc_paper_pids.doi, notes_to_papers.notes, GROUP_CONCAT(tags.name ORDER BY tags_to_papers.timestamp ASC) AS tags, zenodo_code_repos.code_url')
            ->from('pmc_paper')
            ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
            ->leftJoin('tags_to_papers', 'pmc_paper.internal_id = tags_to_papers.paper_id
                AND tags_to_papers.user_id = ' . $this->researcher->user_id)
            ->leftJoin('tags', 'tags.id = tags_to_papers.tag_id')
            ->leftJoin('notes_to_papers', 'pmc_paper.internal_id = notes_to_papers.paper_id
                AND notes_to_papers.user_id = ' . $this->researcher->user_id)
            ->leftJoin('zenodo_code_repos', 'pmc_paper.internal_id = zenodo_code_repos.paper_id')
            ->where(['internal_id' => $ids_subquery])
            ->groupBy('internal_id')
            ->orderBy($orderByClause)
            ->offset($offset)
            ->limit($limit);

        $papers = $papers_query->all();


        // get impact scores
        $papers = SearchForm::get_impact_class($papers);
        // get concepts and scores
        $papers = Concepts::getConcepts($papers, 'internal_id');
        // get impact scores per concept
        $papers = SearchForm::get_concepts_impact_class($papers);
        // get relations
        $papers = Relations::getRelations($papers);

        if ($top_k !== null && !$show_pagination_config) {
            $papers = array_slice($papers, 0, $top_k);
        }

        return [
            'pagination' => $pagination,
            'papers' => $papers,
            'papers_num' => $show_pagination_config
                ? ($top_k !== null ? min($papers_num, (int)$top_k) : $papers_num)
                : count($papers),
        ];

    }

    public function getTopicFacets($topics, $tags, $roles, $accesses, $types, $facet_field) {

        $topics_query = (new \yii\db\Query())
            ->select('concepts.id, concepts.display_name, COUNT(concepts.id) as count')
            ->from('pmc_paper')
            ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
            ->innerJoin('concepts_to_papers', 'pmc_paper.internal_id = concepts_to_papers.paper_id')
            ->innerJoin('concepts', 'concepts.id = concepts_to_papers.concept_id')
            ->where(['doi' => $this->dois]);

            // if (!empty($topics) && strcmp($facet_field, "topic")) {
            //     $topics_query->andWhere([ 'concepts.id' => $topics]);
            // }
            if (!empty($topics) && !in_array($facet_field, ['topic','topics'], true)) {
                $topics_query->andWhere(['concepts.id' => $topics]);
            }

            if (!empty($tags)) {
                $tags_subquery = (new \yii\db\Query())
                    ->select('internal_id')
                    ->from('pmc_paper')
                    ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
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
                    ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
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
            ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
            ->innerJoin('tags_to_papers', 'pmc_paper.internal_id = tags_to_papers.paper_id
                    AND tags_to_papers.user_id = ' . $this->researcher->user_id)
            ->innerJoin('tags', 'tags.id = tags_to_papers.tag_id')
            ->where(['doi' => $this->dois]);

            if (!empty($tags) && !in_array($facet_field, ['tag','tags'], true)) {
                $tags_query->andWhere(['tags.id' => $tags]);
            }

            if (!empty($topics)) {
                $topics_subquery = (new \yii\db\Query())
                    ->select('internal_id')
                    ->from('pmc_paper')
                    ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
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
                    ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
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
            ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
            ->innerJoin('involvement_to_papers', 'pmc_paper.internal_id = involvement_to_papers.paper_id
                    AND involvement_to_papers.user_id = ' . $this->researcher->user_id)
            ->where(['doi' => $this->dois]);

        if (!empty($roles) && !in_array($facet_field, ['role','roles','credit','credit_roles'], true)) {
            $roles_query->andWhere(['involvement' => $roles]);
        }

        if (!empty($topics)) {
            $topics_subquery = (new \yii\db\Query())
                ->select('internal_id')
                ->from('pmc_paper')
                ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
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
                ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
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
            ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
            ->where(['doi' => $this->dois]);

        if (!empty($accesses) && !in_array($facet_field, ['access','accesses','availability','open_access','oa'], true)) {
            $accesses_query->andWhere(['is_oa' => $accesses]);
        }

        if (!empty($topics)) {
            $topics_subquery = (new \yii\db\Query())
                ->select('internal_id')
                ->from('pmc_paper')
                ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
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
                ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
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
                ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
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
            ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
            ->where(['doi' => $this->dois]);

        if (!empty($types) && !in_array($facet_field, ['type','types','work_type','work','publication','publications'], true)) {
            $types_query->andWhere(['type' => $types]);
        }

        if (!empty($accesses)) {
            $types_query->andWhere(['is_oa' => $accesses]);
        }

        if (!empty($topics)) {
            $topics_subquery = (new \yii\db\Query())
                ->select('internal_id')
                ->from('pmc_paper')
                ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
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
                ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
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
                ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
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

    public static function getSelectedPapersForList($list_id) {
        
        $list_id = (int)$list_id;
    
        $json_ids = Yii::$app->db->createCommand("
            SELECT selected_papers
            FROM contributions_list_selections
            WHERE list_id = :lid
            LIMIT 1
        ")
        ->bindValue(':lid', $list_id)
        ->queryScalar();
    
        if (!$json_ids) {
            return [];
        }
    
        $arr = json_decode($json_ids, true);
        if (!is_array($arr)) {
            return [];
        }
    
        return array_map('intval', $arr);
    }
    
    public static function saveSelectedPapersForList($list_id, $paper_ids) {
        $list_id   = (int)$list_id;
        $paper_ids = array_values(array_unique(array_map('intval', (array)$paper_ids)));

        if (empty($paper_ids)) {
            
            $json_ids = json_encode([]);
        } else {
            $json_ids  = json_encode($paper_ids);
        }

        try {
            return Yii::$app->db->createCommand("
                INSERT INTO contributions_list_selections (list_id, selected_papers, created_by)
                VALUES (:lid, :json, :uid)
                ON DUPLICATE KEY UPDATE
                    selected_papers = VALUES(selected_papers),
                    created_by      = VALUES(created_by),
                    updated_at      = CURRENT_TIMESTAMP()
            ")
            ->bindValues([
                ':lid'  => $list_id,
                ':json' => $json_ids,
                ':uid'  => Yii::$app->user->id ?? null,
            ])
            ->execute();
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);
            return 0;
        }
    }
    
}