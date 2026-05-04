<?php

namespace app\models;

use Wamania\Snowball\English;
use Yii;
use yii\base\Model;
use yii\data\Pagination;
use yii\db\Expression;
use yii\helpers\Url;

/**
 * The model behind the search form.
 *
 * @author Thanasis Vergoulis
 */
class SearchForm extends Model {
    public $keywords;

    public $ordering;

    public $location;

    public $relevance;

    public $topics;

    public $start_year;

    public $end_year;

    public $author_kwd;

    public $influence;

    public $popularity;

    public $impulse;

    public $cc;

    public $type;

    public $is_oa;

    public $pubmed_types;

    public $space_model;

    public $provided_by;

    public $annotations = [];

    /*
         * Constructor method to help with validation
         * Also set attributes
         *
         * @return empty string as form identifier.
     *
     * @author Ilias Kanellos
     */
    public function __construct($ordering, $keywords, $location, $relevance = null, $topics = [], $start_year = 0, $end_year = 0, $influence = 'all', $popularity = 'all', $impulse = 'all', $cc = 'all', $type = [], $is_oa = [], $pubmed_types = [], $provided_by = [], $annotations = [], $space_model = null) {
        parent::__construct();
        $this->ordering = $ordering;
        $this->keywords = $keywords;
        $this->relevance = $relevance;
        $this->start_year = intval($start_year);
        $this->end_year = intval($end_year);

        if (! empty($topics)) {
            $this->topics = array_filter($topics);
        } else {
            $this->topics = [];
        }
        $location_parts = explode('-', $location);
        /*
         * Set the selected search to initially empty
         */
        $this->location = [];
        //If abstract/title were specified, insert them
        foreach ($location_parts as $location) {
            if ($location != '') {
                array_push($this->location, $location);
            }
        }

        $this->author_kwd = [];
        $this->influence = $influence;
        $this->popularity = $popularity;
        $this->impulse = $impulse;
        $this->cc = $cc;
        $this->type = $type;
        $this->is_oa = $is_oa;
        $this->pubmed_types = $pubmed_types;
        $this->provided_by = $provided_by;
        $this->annotations = is_array($annotations) ? $annotations : [];
        $this->space_model = $space_model;
    }

    /*
     * Hlias IMPORTANT NOTE: In order to override the name of the form elements,
     * we need to set a custom returned formName function. The default behaviour
     * when we call activeForm is to set the model name as the name of an array
     * and the input element names as the hash keys of that array. E.g. here
     * we would have elements called "SearchForm['<input_element>']". To keep
     * only the name of the input element, we return an empty string
     *
     * @return empty string as form identifier.
     *
     * @author Ilias Kanellos
     */
    public function formName() {
        return '';
    }

    /**
     * Serialize search parameters into a query string for storing in like_dislike_records.
     * This creates a consistent string representation of the search query.
     * Currently matches the format sent by JavaScript (just keywords) for backward compatibility,
     * but can be extended to include all search parameters.
     *
     * @return string Serialized query string
     */
    public function serializeQuery() {
        // For now, return just keywords to match what JavaScript sends
        // This ensures votes are matched by the same query format
        return $this->keywords ?? '';
    }

    /**
     * Get the validation rules.
     *
     * @return array containing the validation rules.
     *
     * @author Thanasis Vergoulis
     */
    public function rules() {
        return [
           //Set the parameter ordering to be null on empty string
           ['ordering', 'default', 'value' => 'popularity'],
           //If both checkboxes were empty, set them both as selected
           ['location', 'default', 'value' => ['title', 'abstract']],
           ['relevance', 'default', 'value' => 'high'],

           // filters default values
           ['influence', 'default', 'value' => 'all'],
           ['popularity', 'default', 'value' => 'all'],
           ['impulse', 'default', 'value' => 'all'],
           ['cc', 'default', 'value' => 'all'],

           ['start_year', 'compare', 'compareValue' => 'end_year', 'operator' => '<=', 'type' => 'number']
       ];
    }

    /**
     * Get the form elements labels.
     *
     * @return array containing the form elements labels.
     *
     * @author Thanasis Vergoulis
     */
    public function attributeLabels() {
        return [
            'keywords' => '',
            'ordering' => 'Ordering:',
            'location' => 'Search in:',
            'cc' => 'Citation Count',
            'provided_by' => 'Provided by',
            'is_oa' => 'Availability',
            'pubmed_types' => 'NLM Types',
            'annotations' => 'Show results with',
        ];
    }

    /*
     * Turn the author keywords into suitable urls for the author pages
     *
     * @return list of html strings
     *
     * @author Ilias Kanellos
     */
    public function process_author_urls() {
        $authors_processed = [];

        foreach ($this->author_kwd as $author) {
            if (strlen($author) >= 2) {
                $author = trim($author);
                array_push($authors_processed, "<a href='" . Url::to(['site/author', 'author' => $author]) . "'>${author}</a>");
            }
        }

        return $authors_processed;
    }

    public static function assignClass($paper, $score, $scores_levels, $impact_type) {
        // papers with no scores (eg datasets)
        if (! isset($paper[$score])) {
            $class = null;
        } elseif ($paper[$score] >= $scores_levels[$impact_type . '_top001']) {
            $class = 'A';
        } elseif ($paper[$score] >= $scores_levels[$impact_type . '_top01']) {
            $class = 'B';
        } elseif ($paper[$score] >= $scores_levels[$impact_type . '_top1']) {
            $class = 'C';
        } elseif ($paper[$score] >= $scores_levels[$impact_type . '_top10']) {
            $class = 'D';
        } else {
            $class = 'E';
        }

        return $class;
    }

    /**
     * Adds the impact class for each entry in $rows.
     *
     * @param $rows The entries (each entry is a paper).
     * @return Returns $rows with included the impact class of each row.
     *
     * @author Thanasis Vergoulis
     */
    public static function get_impact_class($rows) {
        $res = (new \yii\db\Query())->select('*')->from('low_category_scores_view')->one();

        foreach ($rows as $key => $row) {
            $rows[$key]['pop_class'] = self::assignClass($rows[$key], 'attrank', $res, 'popularity');
            $rows[$key]['inf_class'] = self::assignClass($rows[$key], 'pagerank', $res, 'influence');
            $rows[$key]['imp_class'] = self::assignClass($rows[$key], '3y_cc', $res, 'impulse');
            $rows[$key]['cc_class'] = self::assignClass($rows[$key], 'citation_count', $res, 'cc');
        }

        return $rows;
    }

    public static function get_concepts_impact_class($rows) {
        $res = (new \yii\db\Query())->select('*')->from('concepts_low_category_scores_view')->all();
        $res = \yii\helpers\ArrayHelper::index($res, 'concept_id');

        foreach ($rows as $key => $row) {
            foreach ($row['concepts'] as $concept_data) {
                $concept_data['pop_class'] = self::assignClass($rows[$key], 'attrank', $res[$concept_data['id']], 'popularity');
                $concept_data['inf_class'] = self::assignClass($rows[$key], 'pagerank', $res[$concept_data['id']], 'influence');
                $concept_data['imp_class'] = self::assignClass($rows[$key], '3y_cc', $res[$concept_data['id']], 'impulse');
                $concept_data['cc_class'] = self::assignClass($rows[$key], 'citation_count', $res[$concept_data['id']], 'cc');

                // append the element to array
                $concepts_with_impact[] = $concept_data;
            }
            // if a paper has concepts
            if (isset($concepts_with_impact)) {
                $rows[$key]['concepts'] = $concepts_with_impact;
                unset($concepts_with_impact);
            }
        }

        return $rows;
    }

    public static function transformPercentageToClass($percentage) {
        if ($percentage <= 0.01 / 100) {
            $class = 'A';
        } elseif ($percentage <= 0.1 / 100) {
            $class = 'B';
        } elseif ($percentage <= 1 / 100) {
            $class = 'C';
        } elseif ($percentage <= 10 / 100) {
            $class = 'D';
        } else {
            $class = 'E';
        }

        return $class;
    }

    public function getImpactScoreFilters() {
        $last_influence_score = 0;
        $last_popularity_score = 0;
        $last_impulse_score = 0;
        $last_cc_score = 0;

        // if influence filter is set, find the appropriate min influence score to be used in the following queries
        if ($this->influence != 'all') {
            // find the lowest influence score in that category
            $field = 'influence_' . $this->influence;
            $res = (new \yii\db\Query())->select($field)->from('low_category_scores_view')->one();
            $last_influence_score = $res[$field];
        }

        if ($this->popularity != 'all') {
            // find the lowest popularity score in that category
            $field = 'popularity_' . $this->popularity;
            $res = (new \yii\db\Query())->select($field)->from('low_category_scores_view')->one();
            $last_popularity_score = $res[$field];
        }

        if ($this->impulse != 'all') {
            // find the lowest impulse score in that category
            $field = 'impulse_' . $this->impulse;
            $res = (new \yii\db\Query())->select($field)->from('low_category_scores_view')->one();
            $last_impulse_score = $res[$field];
        }

        if ($this->cc != 'all') {
            // find the lowest impulse score in that category
            $field = 'cc_' . $this->cc;
            $res = (new \yii\db\Query())->select($field)->from('low_category_scores_view')->one();
            $last_cc_score = $res[$field];
        }

        return [
            'influence' => $last_influence_score,
            'popularity' => $last_popularity_score,
            'impulse' => $last_impulse_score,
            'cc' => $last_cc_score,
        ];
    }

    public function prepareSearchQuery() {
        // create a Solr select query
        $query = Yii::$app->solr->createSelect();

        // use edismax query parser and query specific fields
        $edismax = $query->getEDisMax();
        $edismax->setQueryFields('title abstract authors doi');

        // return only paper ids and score
        $query->setFields(['internal_id', 'influence', 'popularity', 'impulse', 'citation_count', 'score']);

        // change to 'AND' as default query operator
        $query->setQueryDefaultOperator('AND');

        // set Solr query 'q' parameterer after escaping keywords
        $query->setQuery($this->keywords);

        // add appropriate filters
        if ($this->start_year != 0) {
            $query->createFilterQuery('star_year_filter')->setQuery('year:[' . $this->start_year . ' TO *]');
        }

        if ($this->end_year != 0) {
            $query->createFilterQuery('end_year_filter')->setQuery('year:[* TO ' . $this->end_year . ']');
        }

        if ($this->topics) {
            $topics_filter = [];

            // escape topic names and prepend 'topic' field
            foreach ($this->topics as $key => $topic_name) {
                array_push($topics_filter, 'concepts:' . $query->getHelper()->escapePhrase($topic_name));
            }

            // join them with 'OR' opearator
            $topics_filter = implode(' OR ', $topics_filter);
            $query->createFilterQuery('topics_filter')->setQuery($topics_filter);
        }

        if ($this->type) {
            $type_filter = [];

            // escape type names and prepend 'type' field
            foreach ($this->type as $key => $type_value) {
                array_push($type_filter, 'type:' . $query->getHelper()->escapePhrase($type_value));
            }

            // join them with 'OR' opearator
            $type_filter = implode(' OR ', $type_filter);
            $query->createFilterQuery('type_filter')->setQuery($type_filter);
        }

        if ($this->is_oa) {
            $is_oa_filter = [];

            // escape is_oa names and prepend 'is_oa' field
            foreach ($this->is_oa as $key => $is_oa_value) {
                array_push($is_oa_filter, 'is_oa:' . $query->getHelper()->escapePhrase($is_oa_value));
            }

            // join them with 'OR' opearator
            $is_oa_filter = implode(' OR ', $is_oa_filter);
            $query->createFilterQuery('is_oa_filter')->setQuery($is_oa_filter);
        }

        if ($this->pubmed_types) {
            $pubmed_types_filter = [];

            // escape pubmed_types names and prepend 'pubmed_types' field
            foreach ($this->pubmed_types as $key => $pubmed_types_value) {
                array_push($pubmed_types_filter, 'pubmed_types:' . $query->getHelper()->escapePhrase($pubmed_types_value));
            }

            // join them with 'OR' opearator
            $pubmed_types_filter = implode(' OR ', $pubmed_types_filter);
            $query->createFilterQuery('pubmed_types_filter')->setQuery($pubmed_types_filter);
        }

        // get min impact scores to be added to query (set by impact category filter)
        $min_impact_scores = $this->getImpactScoreFilters();

        if ($this->influence != 'all') {
            $query->createFilterQuery('influence_filter')->setQuery('influence:[' . $min_impact_scores['influence'] . ' TO *]');
        }

        if ($this->popularity != 'all') {
            $query->createFilterQuery('popularity_filter')->setQuery('popularity:[' . $min_impact_scores['popularity'] . ' TO *]');
        }

        if ($this->impulse != 'all') {
            $query->createFilterQuery('impulse_filter')->setQuery('impulse:[' . $min_impact_scores['impulse'] . ' TO *]');
        }

        if ($this->cc != 'all') {
            $query->createFilterQuery('cc_filter')->setQuery('citation_count:[' . $min_impact_scores['cc'] . ' TO *]');
        }

        // Space-specific filters follow Check if solr_name is set
        if (! empty($this->space_model->solr_name)) {
            // 1. Find the research products in the space
            $spaces_filter = [];

            // If $provided_by is set, filter solr_name accordingly
            $solr_names = $this->space_model->solr_name;

            if (! empty($this->provided_by)) {
                // Filter only the entries whose 'value' is in $provided_by
                $solr_names = array_filter($solr_names, function ($space) {
                    return in_array($space['value'], $this->provided_by);
                });
            }

            // Build the Solr filter string
            foreach ($solr_names as $space) {
                $spaces_filter[] = 'spaces:' . $space['value'];
            }

            if (! empty($spaces_filter)) {
                $spaces_filter_str = implode(' OR ', $spaces_filter);
                $query->createFilterQuery('spaces_filter')->setQuery($spaces_filter_str);
            }

            // 2. Filter by space annotations (only if user has selected annotations)
            if (! empty($this->annotations)) {
                $enabled_annotations = $this->space_model->annotations;

                if (! empty($enabled_annotations)) {
                    // Build annotation filter with specific enabled annotation names
                    // Format in Solr: <space_suffix>|<annotation_id>|<enrichment_label>|<enrichment_id>
                    // We match: annotations:<space_suffix>|<annotation_id>|*
                    $annotation_queries = [];

                    // Get selected annotation IDs
                    $selected_ids = array_map('intval', $this->annotations);

                    // Filter to only selected annotation IDs
                    foreach ($enabled_annotations as $annotation) {
                        if (in_array($annotation->id, $selected_ids)) {
                            $annotation_queries[] = 'annotations:' . $this->space_model->url_suffix . '|' . $annotation->id . '|*';
                        }
                    }

                    if (! empty($annotation_queries)) {
                        // Combine with OR to match papers with any selected annotation
                        $annotation_query = '(' . implode(' OR ', $annotation_queries) . ')';
                        $query->createFilterQuery('annotations_filter')->setQuery($annotation_query);
                    }
                }
            }
        }

        // do not consider keyword relevance when:
        // * relevance is set to 'low'
        // * ordering is set to 'year'
        if ($this->relevance == 'low' || $this->ordering == 'year') {
            $query->addSort($this->ordering, $query::SORT_DESC);

        // keyword relevance specified, sort by min-max relevance & impact scores
        } else {
            $max_scores_query = clone $query;

            // sort based on chosen impact
            $max_scores_query->addSort($this->ordering, $max_scores_query::SORT_DESC);

            // Set the number of results to return
            $max_scores_query->setRows(1);

            // Set the 0-based result to start from
            $max_scores_query->setStart(0);
            $result = Yii::$app->solr->select($max_scores_query);
            $response = $result->getData()['response'];

            // get max relevance & impact scores
            $max_relevance_score = $result->getMaxScore();

            $max_relevance_score = ($max_relevance_score) ? $max_relevance_score : 1;

            // no articles found
            if (count($response['docs']) == 0) {
                return null;
            }

            $max_impact_score = $response['docs'][0][$this->ordering];
            $max_impact_score = ($max_impact_score) ? $max_impact_score : 1;

            // form sort clauses based on min-max relavance & impact scores
            $min_sort_clause = 'min(sqrt(sqrt(div(' . $this->ordering . ',' . $max_impact_score . '))),' .
                            'div(query({!edismax v=$q}),' . $max_relevance_score . '))';
            $query->addSort($min_sort_clause, $query::SORT_DESC);

            $max_sort_clause = 'max(sqrt(sqrt(div(' . $this->ordering . ',' . $max_impact_score . '))),' .
                            'div(query({!edismax v=$q}),' . $max_relevance_score . '))';
            $query->addSort($max_sort_clause, $query::SORT_DESC);
        }

        return $query;
    }

    public function prepareSearchResults($paper_ids) {
        // get paper details from the database, join with user likes
        $current_user = (Yii::$app->user->id ? Yii::$app->user->id : 0);

        $rows = (new \yii\db\Query())
            ->select([
                'internal_id',
                'dois_num',
                'pmc_paper_pids.doi',
                'pmc_paper.openaire_id',
                'title',
                'abstract',
                'authors',
                'journal',
                'year',
                'type',
                'is_oa',
                'user_id',
                'attrank',
                'pagerank',
                '3y_cc',
                'citation_count',
            ])
            ->from('pmc_paper')
            ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
            ->leftJoin('users_likes', 'users_likes.paper_id = pmc_paper.internal_id AND users_likes.user_id = ' . addslashes($current_user) . ' AND showit = true')
            ->where(['in', 'internal_id', $paper_ids])
            ->groupBy('internal_id')
            ->orderBy(addslashes($this->getRankingMethod($this->ordering)) . ' DESC')
            ->all();

        return $rows;
    }

    public function search() {
        // prepare the search query
        $query = $this->prepareSearchQuery();

        // no articles found
        if (! $query) {
            return ['rows' => []];
        }

        // execute the search query
        [ $pagination, $paper_ids ] = $this->performSearchQuery($query);

        // get paper details from the database, join with user likes etc
        $rows = $this->prepareSearchResults($paper_ids);

        // highlight keywords in the content
        $rows = $this->get_context($rows);

        // add metadata (concepts, impact classes etc)
        $rows = $this->addMetadata($rows);

        return [
            'rows' => $rows,
            'pagination' => $pagination,
        ];
    }

    /**
     * Get the top topic facets (concepts) from the current search results.
     *
     * Returns the most common topics/concepts found in papers matching
     * the current search query. Results are filtered by all search parameters
     * (keywords, filters, space, etc.).
     *
     * @param int $limit Maximum number of topics to return (default: 5)
     * @return array Associative array of topic_name => count pairs
     */
    public function getTopicsFacet($limit = 5) {
        // prepare the search query
        $query = $this->prepareSearchQuery();

        if (! $query) {
            return [];
        }

        // do not return actual results, only facets
        $query->setRows(0);

        // set the facet field for topics
        $facetSet = $query->getFacetSet();
        $facetField = $facetSet->createFacetField('top_topics')
            ->setField('concepts')
            ->setLimit($limit)
            ->setMinCount(1); // exclude topics with no results

        // Execute the query
        $resultset = Yii::$app->solr->select($query);

        // Get the top 5 topics from the facet results
        $facet_values = $resultset->getFacetSet()->getFacet('top_topics')->getValues();

        return $facet_values;
    }

    /**
     * Get evolution data (counts and citations per year) for a single topic
     * filtered by the current search query.
     *
     * This method filters papers by the selected topic name and returns
     * publication counts and citation counts per year, limited to the
     * latest 20 years. Results are filtered by all current search parameters
     * (keywords, filters, space, etc.).
     *
     * @param string $selected_topic Topic name (concept) to get evolution for
     * @return array [count_per_year, citation_per_year] arrays keyed by year
     */
    public function getTopicEvolution($selected_topic) {
        // prepare the search query
        $query = $this->prepareSearchQuery();

        if (! $query) {
            return [[], []];
        }

        // set the filter for the selected topic
        $selected_topic_condition = 'concepts:' . $query->getHelper()->escapePhrase($selected_topic);
        $query->createFilterQuery('selected_topic_condition')->setQuery($selected_topic_condition);

        // Use reusable method to get evolution data
        list($count_per_year, $citation_per_year) = $this->getEvolutionData($query);

        return [
            $count_per_year,
            $citation_per_year
        ];
    }

    /**
     * Get the top annotation facets from the current search results.
     *
     * Returns the most common annotations found in papers matching the current
     * search query. Supports filtering by annotation type via annotation_type_id.
     * Results are filtered by all search parameters (keywords, filters, space, etc.).
     *
     * Annotations are aggregated by enrichment_label (the display name), so multiple
     * annotation IDs with the same label are combined into a single count.
     *
     * @param int $limit Maximum number of annotations to return (default: 5)
     * @param int|null $annotation_type_id Optional annotation type ID to filter by (null = all types)
     * @return array Associative array of annotation_name => count pairs
     */
    public function getAnnotationsFacet($limit = 5, $annotation_type_id = null) {
        // prepare the search query
        $query = $this->prepareSearchQuery();

        if (! $query) {
            return [];
        }

        // Only show annotations if we're in a space with annotations enabled
        if (! $this->space_model || empty($this->space_model->annotations)) {
            return [];
        }

        // do not return actual results, only facets
        $query->setRows(0);

        // set the facet field for annotations
        $facetSet = $query->getFacetSet();
        $space_suffix = $this->space_model->url_suffix;

        // Determine facet prefix based on whether a specific annotation type is selected
        // Format: <space_suffix>|<annotation_id>|<enrichment_label>|<enrichment_id>
        if ($annotation_type_id !== null && $annotation_type_id !== 'all') {
            // Filter facets to a specific annotation type
            $annotation_type_id = (int) $annotation_type_id;
            $facet_prefix = $space_suffix . '|' . $annotation_type_id . '|';
        } else {
            // Get all annotations from this space (no type filter)
            $facet_prefix = $space_suffix . '|';
        }

        // Create facet field with prefix filtering at Solr level
        // Note: We keep the sidebar filter (annotations_filter) active, so facets are counted
        // only from papers that pass the sidebar filter
        // Create facet field with prefix filtering at Solr level
        $facetField = $facetSet->createFacetField('top_annotations')
            ->setField('annotations')
            ->setPrefix($facet_prefix)
            ->setLimit($limit)
            ->setMinCount(1);

        // Execute the query
        $resultset = Yii::$app->solr->select($query);

        // Get the facet results
        // Note: Annotations in Solr are unique, so no aggregation needed
        $facet_values = $resultset->getFacetSet()->getFacet('top_annotations')->getValues();

        // Parse annotation values to extract enrichment labels
        // Format in Solr: <space_suffix>|<annotation_id>|<enrichment_label>|<enrichment_id>
        // Note: facet.prefix already filtered facets to match our space/type, so we can trust the format
        $annotation_counts = [];
        $annotation_types = [];

        // Store the selected annotation_type_id (if not 'all')
        $selected_annotation_id = ($annotation_type_id !== null && $annotation_type_id !== 'all') ? (int) $annotation_type_id : null;

        foreach ($facet_values as $annotation_value => $count) {
            // Parse the annotation value
            $parts = explode('|', $annotation_value);

            // Safety check: ensure we have the expected format
            if (count($parts) < 4) {
                continue;
            }

            $solr_annotation_id = (int) $parts[1];
            $enrichment_label = $parts[2];

            // Use count directly (no aggregation since annotations are unique)
            $annotation_counts[$enrichment_label] = $count;

            // Track the type ID for this annotation
            $annotation_types[$enrichment_label] = $selected_annotation_id !== null
                ? $selected_annotation_id
                : $solr_annotation_id;
        }

        // Solr already returns facets sorted by count descending, so no need to sort again

        return ['counts' => $annotation_counts, 'types' => $annotation_types];
    }

    /**
     * Get evolution data (counts and citations per year) for all annotations of a specific type
     * filtered by the current search query.
     *
     * This method filters annotations by annotation type ID from the current search results,
     * then returns publication counts and citation counts per year for all annotations
     * of that type. Results are filtered by all current search parameters (keywords, filters, space, etc.).
     *
     * Note: This differs from getAnnotationEvolution() which:
     * - Uses specific annotation IDs (not type IDs)
     * - Is not filtered by search query (shows all papers with that annotation)
     * - Is a static method
     *
     * @param int $annotation_type_id Annotation type ID to get evolution for (e.g., 3=Diseases, 4=Drugs)
     * @return array [count_per_year, citation_per_year] arrays keyed by year
     */
    public function getTopAnnotationEvolution($annotation_type_id) {
        // prepare the search query
        $query = $this->prepareSearchQuery();

        if (! $query) {
            return [[], []];
        }

        // Only show annotations if we're in a space with annotations enabled
        if (! $this->space_model || empty($this->space_model->annotations)) {
            return [[], []];
        }

        // Use regex filter query to match annotations by annotation_type_id
        // This is much more efficient than fetching 1000 facets and filtering in PHP
        // Format: <space_suffix>|<annotation_id>|<enrichment_label>|<enrichment_id>
        $space_suffix = $this->space_model->url_suffix;
        $escaped_space_suffix = preg_quote($space_suffix, '/');
        $escaped_annotation_type_id = preg_quote((string) $annotation_type_id, '/');

        // Build regex filter: match any annotation with this space and annotation_type_id
        // Pattern allows any enrichment_label and enrichment_id
        $annotation_filter = 'annotations:/' . $escaped_space_suffix . '\\|' . $escaped_annotation_type_id . '\\|.*\\|.*/';

        $query->createFilterQuery('selected_annotation_condition')->setQuery($annotation_filter);
        $query->setRows(0);

        // Use reusable method to get evolution data
        list($count_per_year, $citation_per_year) = $this->getEvolutionData($query);

        return [$count_per_year, $citation_per_year];
    }

    /**
     * Get evolution data (counts and citations per year) for the top N topics
     * filtered by the current search query.
     *
     * This method gets the top topics from search results, then calculates
     * evolution data for each topic. Results are filtered by all current search
     * parameters. Returns data for multiple topics to enable comparison charts.
     *
     * The data is normalized across all topics (fills missing years with zeros)
     * and limited to the latest 10 years for visualization purposes.
     *
     * @param int $limit Number of top topics to get evolution for (default: 5)
     * @return array Associative array with:
     *               - 'counts': array of topic_name => count_per_year arrays
     *               - 'citations': array of topic_name => citation_per_year arrays
     */
    public function getTopTopicsEvolution($limit = 5) {
        // Get top topics first
        $top_topics = $this->getTopicsFacet($limit);

        if (empty($top_topics)) {
            return [
                'counts' => [],
                'citations' => []
            ];
        }

        // Prepare base query
        $base_query = $this->prepareSearchQuery();

        if (! $base_query) {
            return [
                'counts' => [],
                'citations' => []
            ];
        }

        $topics_evolution = [];
        $topics_citations = [];
        $all_years = [];

        // Get evolution for each topic
        foreach ($top_topics as $topic_name => $facet_count) {
            // Clone the base query for this topic
            $query = clone $base_query;

            // Set filter for this topic
            $topic_condition = 'concepts:' . $query->getHelper()->escapePhrase($topic_name);
            $query->createFilterQuery('topic_condition')->setQuery($topic_condition);

            // Use reusable method to get evolution data
            list($count_per_year, $citation_per_year) = $this->getEvolutionData($query);

            // Collect all years
            $all_years = array_merge($all_years, array_keys($count_per_year));

            $topics_evolution[$topic_name] = $count_per_year;
            $topics_citations[$topic_name] = $citation_per_year;
        }

        // Get the range of years across all topics
        if (empty($all_years)) {
            return [
                'counts' => [],
                'citations' => []
            ];
        }

        $minYear = min($all_years);
        $maxYear = max($all_years);

        // Fill missing years with zero for each topic (both counts and citations)
        foreach ($topics_evolution as $topic_name => &$count_per_year) {
            for ($year = $minYear; $year <= $maxYear; $year++) {
                if (! isset($count_per_year[$year])) {
                    $count_per_year[$year] = 0;
                }
            }
            ksort($count_per_year);
        }
        unset($count_per_year);

        foreach ($topics_citations as $topic_name => &$citation_per_year) {
            for ($year = $minYear; $year <= $maxYear; $year++) {
                if (! isset($citation_per_year[$year])) {
                    $citation_per_year[$year] = 0;
                }
            }
            ksort($citation_per_year);
        }
        unset($citation_per_year);

        // Keep only the latest 10 years
        foreach ($topics_evolution as $topic_name => &$count_per_year) {
            $count_per_year = array_slice($count_per_year, -10, 10, true);
        }
        unset($count_per_year);

        foreach ($topics_citations as $topic_name => &$citation_per_year) {
            $citation_per_year = array_slice($citation_per_year, -10, 10, true);
        }
        unset($citation_per_year);

        return [
            'counts' => $topics_evolution,
            'citations' => $topics_citations
        ];
    }

    /**
     * Get evolution data (counts and citations per year) for a specific annotation
     * from annotation detail pages (NOT filtered by search results).
     *
     * This static method is used for annotation detail pages where we want to show
     * evolution for ALL papers with a specific annotation, regardless of any search
     * filters. It uses specific annotation identifiers (space_url_suffix, annotation_id, id)
     * rather than annotation names from search facets.
     *
     * Note: This differs from getTopAnnotationEvolution() which:
     * - Filters by current search query
     * - Uses annotation names from search facets
     * - Is an instance method
     *
     * @param string $space_url_suffix Space URL suffix
     * @param int $annotation_id Space annotation ID
     * @param string $id Annotation ID (e.g., DOID:0050687)
     * @return array [count_per_year, citation_per_year] arrays keyed by year
     */
    public static function getAnnotationEvolution($space_url_suffix, $annotation_id, $id) {
        // Prepare annotation query
        $query = self::prepareAnnotationQuery($space_url_suffix, $annotation_id, $id, 'popularity');

        // Create a temporary SearchForm instance to use the protected method
        $searchForm = new self('popularity', '', '', null, [], 0, 0);
        list($count_per_year, $citation_per_year) = $searchForm->getEvolutionData($query);

        return [
            $count_per_year,
            $citation_per_year
        ];
    }

    public function addMetadata($rows) {
        // add the impact class of each row
        $rows = self::get_impact_class($rows);
        // get concepts and scores
        $rows = Concepts::getConcepts($rows, 'internal_id');
        // get impact scores per concept
        $rows = self::get_concepts_impact_class($rows);
        // get annotations, only if we are in a space
        if ($this->space_model) {
            $rows = Spaces::fetchAnnotations($rows, $this->space_model);
        }

        // get relations
        $rows = Relations::getRelations($rows);
        // get pubmed types
        $rows = PubmedTypes::getPubmedTypes($rows, $this->space_model);

        // attach code repository URLs
        $rows = Article::getCodeRepoUrls($rows);

        return $rows;
    }

    public function searchById($id) {
        $current_user = (Yii::$app->user->id ? Yii::$app->user->id : 0);
        //Build the search query.
        $query = (new \yii\db\Query())
            ->select(['internal_id', 'doi', 'openaire_id', 'title', 'journal', 'year', 'pagerank', 'pagerank_normalized', 'attrank', 'attrank_normalized', '3y_cc', '3y_cc_normalized', 'citation_count', 'citation_count_normalized', 'user_id'])
            ->from('pmc_paper')
            ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
            ->leftJoin('users_likes', 'users_likes.paper_id = pmc_paper.internal_id AND users_likes.user_id = ' . addslashes($current_user) . ' AND showit = true')
            ->where('internal_id=' . $id)
            ->groupBy('internal_id');

        //Get the results of the page.
        $rows = $query->all();

        return $rows[0];
    }

    public function count_filters() {
        $count = 0;

        if (count($this->topics) > 0) {
            $count++;
        }

        if (count($this->type) > 0) {
            $count++;
        }

        if (count($this->is_oa) > 0) {
            $count++;
        }

        if (count($this->pubmed_types) > 0) {
            $count++;
        }

        if ($this->start_year != 0) {
            $count++;
        }

        if ($this->end_year != 0) {
            $count++;
        }

        if ($this->influence != 'all') {
            $count++;
        }

        if ($this->popularity != 'all') {
            $count++;
        }

        if ($this->impulse != 'all') {
            $count++;
        }

        if ($this->cc != 'all') {
            $count++;
        }

        if (! empty($this->provided_by)) {
            $count++;
        }

        // Check if annotations filter is enabled (annotations array is not empty)
        if (! empty($this->annotations)) {
            $count++;
        }

        return $count;
    }

    public static function getSimilarArticles($paper_id) {
        // create a Solr select query
        $query = Yii::$app->solr->createSelect();

        // add a query and morelikethis settings
        $query->setQuery('internal_id:"' . $paper_id . '"')
            ->getMoreLikeThis()
            ->setFields('title, abstract')  // these must be indexed in Solr with termVectors=true
            ->setMinimumTermFrequency(1)
            ->setCount(20);
        $query->addParam('json.nl', 'map');
        $query->setRows(1);

        $result = Yii::$app->solr->select($query);
        $mlt = $result->getMoreLikeThis();
        $response = $result->getData()['moreLikeThis'][$paper_id];
        $similar_paper_ids = array_column($response['docs'], 'internal_id');

        // get details for the similar papers
        $current_user = (Yii::$app->user->id ? Yii::$app->user->id : 0);

        return (new \yii\db\Query())
            ->select('internal_id, doi, title, authors, journal, year, user_id')
            ->from('pmc_paper')
            ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
            // needed to show if already bookmarked
            ->leftJoin('users_likes', 'users_likes.paper_id = pmc_paper.internal_id AND users_likes.user_id = ' . addslashes($current_user) . ' AND showit = true')
            ->where(['internal_id' => $similar_paper_ids])
            ->groupBy('internal_id')
            ->all();
    }

    public static function getReadings($dois) {
        $rows = (new \yii\db\Query())
            ->select(['doi', 'attrank', 'pagerank'])
            ->from('pmc_paper')
            ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
            ->where(['in', 'doi', $dois])
            ->all();

        $rows = self::get_impact_class($rows);

        return $rows;
    }

    /**
     * Get the ranking field name for a given ordering method.
     *
     * @param string $method Ordering method (popularity, influence, etc.)
     * @return string Database/Solr field name
     */
    public static function getRankingField($method) {
        if (empty($method) || ! isset(Yii::$app->params['impact_fields'][$method])) {
            return 'attrank'; // Default to popularity
        }

        return Yii::$app->params['impact_fields'][$method];
    }

    /**
     * Prepare Solr query for annotation search.
     * Pattern: annotations:"<space_url_suffix>|<annotation_id>|*|<id>"
     * Using regex to allow any value for the name field (third position).
     *
     * @param string $space_url_suffix Space URL suffix
     * @param int $annotation_id Space annotation ID
     * @param string $id Annotation ID (e.g., DOID:0050687)
     * @param string $ordering Ordering method (popularity, influence, citation_count, impulse, year)
     * @return \Solarium\QueryType\Select\Query\Query Solr query object
     */
    public static function prepareAnnotationQuery($space_url_suffix, $annotation_id, $id, $ordering = 'popularity') {
        $solr_query = Yii::$app->solr->createSelect();

        // Escape special characters for regex
        $escaped_id = preg_quote($id, '/');
        $escaped_space_suffix = preg_quote($space_url_suffix, '/');
        $escaped_annotation_id = preg_quote($annotation_id, '/');

        // Build the annotation filter using regex to match the pattern
        // Pattern: <space_url_suffix>|<annotation_id>|<any_name>|<id>
        $annotation_filter = 'annotations:/' . $escaped_space_suffix . '\\|' . $escaped_annotation_id . '\\|.*\\|' . $escaped_id . '/';

        $solr_query->createFilterQuery('annotation_filter')->setQuery($annotation_filter);
        // Include fields needed for faceting and stats
        $solr_query->setFields(['internal_id', 'year', 'citation_count']);

        // Add sorting based on ordering
        // $sort_field = self::getRankingField($ordering);
        $solr_query->addSort($ordering, $solr_query::SORT_DESC);

        return $solr_query;
    }

    /**
     * Execute annotation Solr query and return pagination and internal IDs.
     *
     * @param \Solarium\QueryType\Select\Query\Query $query Solr query object
     * @param int $pageSize Page size for pagination
     * @return array [Pagination, array of internal_ids]
     */
    public static function performAnnotationQuery($query, $pageSize = 20) {
        // Create pagination object with temporary totalCount
        // We'll update it after getting the actual count from Solr response
        $pagination = new Pagination([
            'pageSize' => $pageSize,
            'totalCount' => 50000000000, // Temporary high value, will be updated
        ]);

        // Set pagination parameters and execute query
        $query->setRows($pagination->limit);
        $query->setStart($pagination->offset);
        $result = Yii::$app->solr->select($query);
        $response = $result->getData()['response'];

        // Update pagination with actual total count from Solr response
        $pagination->totalCount = $response['numFound'];

        // Extract internal_ids from response
        $internal_ids = [];

        if (! empty($response['docs'])) {
            $internal_ids = array_column($response['docs'], 'internal_id');
        }

        return [
            $pagination,
            $internal_ids
        ];
    }

    /**
     * Prepare annotation search results from database.
     * Gets paper details for the given internal IDs, preserving order.
     *
     * @param array $internal_ids Array of internal IDs
     * @return array Array of paper records
     */
    public static function prepareAnnotationResults($internal_ids) {
        if (empty($internal_ids)) {
            return [];
        }

        $current_user = (Yii::$app->user->id ? Yii::$app->user->id : 0);

        $works = (new \yii\db\Query())
            ->select(['internal_id', 'dois_num', 'doi', 'title', 'authors', 'journal', 'year', 'type', 'is_oa', 'user_id', 'attrank', 'pagerank', '3y_cc', 'citation_count'])
            ->from('pmc_paper')
            ->innerJoin('pmc_paper_pids', 'pmc_paper.internal_id = pmc_paper_pids.paper_id')
            ->leftJoin('users_likes', 'users_likes.paper_id = pmc_paper.internal_id AND users_likes.user_id = ' . addslashes($current_user) . ' AND showit = true')
            ->where(['in', 'internal_id', $internal_ids])
            ->groupBy('internal_id')
            ->orderBy([new Expression('FIELD(internal_id, ' . implode(',', array_map(function ($element) { return (int) $element; }, $internal_ids)) . ')')])
            ->all();

        // Add impact classes
        $works = self::get_impact_class($works);

        // Get concepts and scores
        $works = Concepts::getConcepts($works, 'internal_id');

        // Get impact scores per concept
        $works = self::get_concepts_impact_class($works);

        return $works;
    }

    /**
     * Get evolution data (counts and citations per year) for a given Solr query.
     *
     * This is a reusable helper method that extracts year-based evolution data
     * from any Solr query. It:
     * - Adds year faceting to count papers per year
     * - Adds citation statistics grouped by year
     * - Fills missing years with zeros
     * - Limits results to the latest 20 years
     *
     * Used by getTopicEvolution(), getTopAnnotationEvolution(), and
     * getAnnotationEvolution() to avoid code duplication.
     *
     * @param \Solarium\QueryType\Select\Query\Query $query Solr query object (should already have filters applied)
     * @return array [count_per_year, citation_per_year] arrays keyed by year
     */
    protected function getEvolutionData($query) {
        // Ensure query doesn't return actual results, only facets
        $query->setRows(0);

        // Add faceting on year
        $facetSet = $query->getFacetSet();
        $facet = $facetSet->createFacetField('years_facet')
            ->setField('year')
            ->setMinCount(1);

        // Add statistics for citation counts
        $statsComponent = $query->getStats();
        $statsComponent->createField('citation_count')->addFacet('year');

        // Execute the query
        $resultset = Yii::$app->solr->select($query);

        // Get the count per year from facet results
        $count_per_year = $resultset->getFacetSet()->getFacet('years_facet')->getValues();

        // Get the citation count statistics per year
        $stats = $resultset->getStats()->getResult('citation_count')->getFacets();
        $citation_per_year_raw = $stats['year'] ?? [];

        // Process citation data - convert stat objects to sums
        $citation_per_year = [];

        foreach ($citation_per_year_raw as $year => $stat) {
            $citation_per_year[$year] = $stat->getSum();
        }

        // Determine the range of years
        if (empty($count_per_year) && empty($citation_per_year)) {
            return [[], []];
        }

        $all_years = array_unique(array_merge(
            array_keys($count_per_year),
            array_keys($citation_per_year)
        ));

        if (empty($all_years)) {
            return [[], []];
        }

        $minYear = min($all_years);
        $maxYear = max($all_years);

        // Fill missing years with zero for both counts and citations
        for ($year = $minYear; $year <= $maxYear; $year++) {
            if (! isset($count_per_year[$year])) {
                $count_per_year[$year] = 0;
            }

            if (! isset($citation_per_year[$year])) {
                $citation_per_year[$year] = 0;
            }
        }

        // Sort the arrays by keys (years)
        ksort($count_per_year);
        ksort($citation_per_year);

        // Keep only the latest 20 years
        $count_per_year = array_slice($count_per_year, -20, 20, true);
        $citation_per_year = array_slice($citation_per_year, -20, 20, true);

        return [$count_per_year, $citation_per_year];
    }

    /*
     * Get the actual ordering method (column name)
     *
     * @return the actual db column name to order results by.
     *
     * @author Ilias Kanellos
     */
    private function getRankingMethod($method) {
        return Yii::$app->params['impact_fields'][$method];
    }

    /*
     * Returns whether string starts with punctiation mark
     *
     * @return boolean
     *
     * @author Ilias Kanellos
     */
    private function starts_with_punctuation_mark($string) {
        if (substr($string, 0, 1) == '.' || substr($string, 0, 1) == '!' ||
               substr($string, 0, 1) == '?' || substr($string, 0, 1) == ':' ||
               substr($string, 0, 1) == ';') {
            return true;
        }

        return false;
    }

    /*
     * Removes first word if it starts with a punctuation mark
     *
     * @return string
     *
     * @author Ilias Kanellos
     */
    private function remove_first_word($string) {
        $string = explode(' ', $string);
        array_shift($string);
        $string = implode(' ', $string);
        $string = '[...] ' . $string;

        return $string;
    }

    /*
     * Returns an array of phrases containing the particular keyword searched
     *
     * @return array of strings
     *
     * @author Ilias Kanellos
     */
    private function get_kwd_matches($article_data, $keyword, $lowercase_it = true, $add_brackets = true) {
        if ($lowercase_it) {
            $kwd_to_search = strtolower($keyword);
        } else {
            $kwd_to_search = $keyword;
        }

        //ATTENTION: HERE WE ADDED POSSIBLE END OF STRING CHARACTER TO END OF MATCH.
        //CHECK IF THIS LEAVES THINGS WORKING AS INTENDED. IF CONTEXT DOESNT SEEM RIGHT,
        //REMOVE THE FINAL $ FROM THE REGULAR EXPRESSION (also the parentheses and the logical "or")
        preg_match_all("/(^|[\.\?!:;])[^\.\?!:;]*?" . preg_quote($kwd_to_search, '/') . "[^\.\?!:;]*?([\.\?!:;]|$)/i", $article_data, $kwd_matches);
        $context_array = [];
        //Add a sentence each time to context array
        if (! empty($kwd_matches[0])) {
            foreach ($kwd_matches[0] as $match) {
                if (substr($match, 1, 1) === ' ') {
                    $match = substr($match, 2);
                }

                if ($this->starts_with_punctuation_mark($match)) {
                    $match = $this->remove_first_word($match);
                }
                $match = trim($match);

                if ($add_brackets) {
                    $match = preg_replace('/[\.\?!:;]$/', '', $match) . ' [...]';
                }
                $match = htmlspecialchars($match);
                array_push($context_array, $match);
            }
        }

        return $context_array;
    }

    private static function split_and_stem_keywords($keywords) {
        $stemmer = new English();

        $kwds = preg_split("/\s+/", preg_replace('/[\'"]+/', '', trim($keywords)));
        $stemmed_keywords = [];

        foreach ($kwds as $kwd) {
            $stem = $stemmer->stem($kwd);

            if ($stem != $kwd) {
                array_push($stemmed_keywords, $stem);
            }
        }

        return array_merge($kwds, $stemmed_keywords);
    }

    /**
     * ???
     */
    private function get_context($rows) {
        $keywords = self::split_and_stem_keywords($this->keywords);

        foreach ($rows as $key => $row) {
            $rows[$key]['search_context'] = [];

            //Get context in abstract
            $article_contexts_abstract = $this->get_kwd_context($row['internal_id'], 'abstract', $keywords);
            $article_contexts_abstract = $this->enclose_kwds_in_span($article_contexts_abstract, true, $keywords);
            $rows[$key]['search_context']['abstract'] = $article_contexts_abstract;

            //Get context in title
            $article_contexts_title = $this->get_kwd_context($row['internal_id'], 'title', $keywords);
            $article_contexts_title = $this->enclose_kwds_in_span($article_contexts_title, true, $keywords);
            $rows[$key]['search_context']['title'] = $article_contexts_title;

            //Get kwd in author field!
            $contexts_authors = $this->get_author_context($row['internal_id'], $keywords);
            $contexts_authors = $this->enclose_kwds_in_span($contexts_authors, false, $keywords);
            $rows[$key]['search_context']['author'] = $contexts_authors;
        }

        return $rows;
    }

    /**
     * Get the context of found keywords.
     *
     * @return an array containing the context of the keywords found in a paper
     *
     * @author Ilias Kanellos
     */
    private function get_kwd_context($paper_id, $field = 'abstract', $kwd_array) {
        if ($field != 'abstract' && $field != 'title') {
            $field = 'abstract';
        }
        //Get the article object and return it's text.
        $article_data = Article::find()->select($field)->where(['internal_id' => $paper_id])->one();
        $article_data = $article_data[$field];

        //Context array contains sentenctes with the context
        $context_array = [];

        //Get the context for each kwd
        foreach ($kwd_array as $keyword) {
            //Get all matches, add them to context_array
            $contexts = $this->get_kwd_matches($article_data, $keyword);
            $context_array = array_merge($context_array, $contexts);
        }
        //Remove duplicates from context
        $context_array = array_unique($context_array);

        //Return contexts
        return $context_array;
    }

    private function get_author_context($paper_id, $kwd_array) {
        //Get the article object and return it's text.
        $article_authors = Article::find()->select('authors')->where(['internal_id' => $paper_id])->one();
        $article_authors = $article_authors['authors'];
        //$author_list = str_split($article_authors, ",");

        $context_array = [];

        foreach ($kwd_array as $keyword) {
            //Get all matches, add them to context_array
            $keyword = ucfirst($keyword);
            $contexts = $this->get_kwd_matches($article_authors, $keyword, false, false);
            $context_array = array_merge($context_array, $contexts);

            if (! empty($context_array)) {
                if (! in_array($keyword, $this->author_kwd)) {
                    array_push($this->author_kwd, $keyword);
                }
            }
        }
        //Remove duplicates from context
        $context_array = array_unique($context_array);
        //Return contexts
        return $context_array;
    }

    private function enclose_kwds_in_span($context_array, $lc_first = true, $keywords) {
        if (! $lc_first) {
            $keywords = array_map('ucfirst', $keywords);
        }

        foreach ($context_array as $offset => $context) {
            foreach ($keywords as $kwd) {
                if ($lc_first) {
                    $kwd = lcfirst($kwd);
                }
                $context_array[$offset] = str_ireplace($kwd, "<span class='highlight-kwd'>${kwd}</span>", $context_array[$offset]);
            }
        }

        return $context_array;
    }

    private function performSearchQuery($query) {
        // create the pagination object
        // if we do not pass 'totalCount' in the constructor, 'limit' & 'offset' below are always for page 0
        // so we pass a high value here and update 'totalCount' after querying Solr with the correct value
        $pagination = new Pagination([
            'totalCount' => 50000000000
        ]);

        // Set the number of results to return
        $query->setRows($pagination->limit);

        // Set the 0-based result to start from
        $query->setStart($pagination->offset);

        // execute the query
        $result = Yii::$app->solr->select($query);
        $response = $result->getData()['response'];

        $pagination->totalCount = $response['numFound'];

        // keep only paper ids from response
        $paper_ids = array_column($response['docs'], 'internal_id');

        return [
            $pagination,
            $paper_ids
        ];
    }
}
