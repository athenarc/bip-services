<?php

    namespace app\models;
    use Yii;
    use yii\httpclient\Client;
    use app\models\ChartData;

    /**
     * The model of pmc_papers.
     *
     * @author Thanasis Vergoulis
     */
    class Article extends \yii\db\ActiveRecord
    {
        /**
         * The following properties might be set if we need to know
         * the paper's position in the top-k most popular/influential
         * papers overall, or in the journal.
         */
        public $pop_journal;
        public $pop_journal_class;
        public $pop_total;
        public $inf_journal;
        public $inf_journal_class;
        public $inf_total;
        public $imp_journal;
        public $imp_journal_class;
        public $cc_journal;
        public $cc_journal_class;
        public $citation_history;
        public $bibtex;
        public $pdf_link;
        public $views;
        public $likes;
        public $readers;
        public $pop_class;
        public $inf_class;
        public $imp_class;
        public $cc_class;
        public $concepts;
        public $chart_data = [];

        /**
         * It returns the minimum percentile in which the paper belongs (based on its score).
         *
         * @param $num_ranked_higher The number of papers ranked higher than the current paper
         * @param $num_overall The total number of papers.
         *
         * @author Ilias Kanellos, Thanasis Vergoulis
         */
        public static function getTopPercentage($num_ranked_higher, $num_overall)
        {
            $percentage = ($num_ranked_higher / $num_overall);
            return $percentage;
        }

        public static function tableName()
        {
            return 'pmc_paper';
        }

        public function attributeLabels()
        {
            return [
                'journal' => 'Venue',
                'title' => 'Title',
                'pmc' => 'PMC id',
                'authors' => 'Authors',
                'type' => 'Type',
                'doi' => 'Persistent identifier',
                'references' => 'References',
                'abstract_score' => 'Readability score',
                'pdf_link' => 'PDF Link'
            ];
        }

        /*
         * Declare a relation to user_likes (there will be an article id in the likes)
         */
        public function getUsersLikes()
        {
            return $this->hasMany(UsersLikes::className(), ['paper_id' => 'internal_id']);
        }

        public function getNumLikes()
        {
            return $this->hasMany(UsersLikes::className(), ['paper_id' => 'internal_id'])->andOnCondition(['showit' => true])->count();
        }

        /*
         * Added by Ilias (October 2017)
         *
         * Connects paper record with citation history record.
         */
        public function getCitationHistory()
        {
            return $this->hasMany(PaperCitationHistories::className(), ['internal_id' => 'internal_id']);
        }

        /*
         * Functions to return number of guest/user views for article
         *
         * @author: Hlias
         *
         * each paper has many records for users and guest that viewed its details
         */
        public function getGuestViews()
        {
            return $this->hasMany(GuestViews::className(), ['paper_id' => 'internal_id'])->count();
        }

        public function getUserViews()
        {
            return $this->hasMany(UserViews::className(), ['paper_id' => 'internal_id'])->count();
        }

        /*
         * Get the sum of user + guest views for all papers.
         * Get the paper with the maximum sum of those
         */
        public static function getMostViewedPaper()
        {
            //Statement to get paper views from guests
            $guest_views = (new \yii\db\Query())
            ->select("paper_id")
            ->from('guest_views')
            ->createCommand()
            ->sql;

            /*
            $user_views = (new \yii\db\Query())
            ->select("paper_id")
            ->from('user_views')
            ->createCommand()->sql;
            */

            //Statement to combine guest + user views
            $total_views_union = (new \yii\db\Query())
            ->select('paper_id')
            ->from('user_views')
            ->union($guest_views, true)
            ->createCommand()->sql;

            $total_views = (new \yii\db\Query())
            ->select('paper_id, count(paper_id) as views')
            ->from("(" . $total_views_union . ") as union_table")
            ->groupBy('paper_id')
            ->createCommand()->sql;

            $max_view = (new \yii\db\Query())
            ->select('paper_id, views')
            ->from("(" . $total_views . ") as paper_views")
            ->orderBy(['views' => SORT_DESC])
            ->one();

            return $max_view;
    }

    // DEPRECATED
    // public static function getMaxPaperViews() {
    //     $duration_expiry = 86400; // cache gets invalided every day

    //     $max_paper_views = Yii::$app->cache->getOrSet('max_paper_views', function () {

    //         $user_max_views = (new \yii\db\Query())
    //           ->select('paper_id, count(*) as max_views')
    //           ->from('user_views')
    //           ->groupBy('paper_id')
    //           ->orderBy(['max_views' => SORT_DESC])
    //           ->one();

    //         $guest_max_views = (new \yii\db\Query())
    //           ->select('paper_id, count(*) as max_views')
    //           ->from('guest_views')
    //           ->groupBy('paper_id')
    //           ->orderBy(['max_views' => SORT_DESC])
    //           ->one();

    //         $max_paper_views = $user_max_views['max_views'] + $guest_max_views['max_views'];

    //         return ($max_paper_views == 0) ? 1 : $max_paper_views;
    //     }, $duration_expiry);

    //     return $max_paper_views;
    // }

    /*
     * Find the total views of all papers and return the % for a particular one
     */
     public static function getPaperViewPercentage($internal_id)
     {
         $article = Article::find()->where(['internal_id' => $internal_id])->one();
         $guest_views = $article->getGuestViews();
         $user_views  = $article->getUserViews();

         $paper_views = $guest_views+$user_views;

         return $paper_views;

     }

     /*
      * find all journals in the database
      *
      * @returns array of journals
      *
      * @author Ilias Kanellos
      */
     public static function getJournalNamesAutoComplete($expansion, $max_num, $term)
     {
        //Check on max num variable. Should be number
        $max_num == '' ? $max_num = 7 : $max_num;
        //Get whether to check left or right
        $expansion = strtolower($expansion);
        if($expansion == "left")
        {
            $search_type = '%' . $term;
        }
        else if($expansion == 'right')
        {
            $search_type = $term . '%';
        }
        else
        {
            $search_type = '%' . $term . '%';
        }

        $journal_names = Article::find()->select("journal")->distinct()->
                         where(['like', 'journal', $search_type, false])->
                         limit($max_num)->asArray()->all();
        //Return json. Don't forget to remove any other strings echoed for testing.
        return json_encode(array_map("array_pop", $journal_names));
     }

    // public static function getCitationCount($pmc_id){
    // 	$result = (new \yii\db\Query())
    // 		->select('citation_count')
    // 		->from('pmc_paper')
    // 		->where(['pmc' => $pmc_id])
    // 		->one();

    // 	return $result['citation_count'];
    // }
     /*
        Find all references of this pmc id
        @author: Serafeim Chatzopoulos
     */
     public static function getReferences($paper_id){
        $cited = (new \yii\db\Query())
            ->select('cited')
            ->from('citation_graph')
            ->where(['citing' => $paper_id])
            ->orderBy(['cited' => SORT_ASC]);

        $current_user = (Yii::$app->user->id ? Yii::$app->user->id : 0);

        return (new \yii\db\Query())
            ->select('internal_id, doi, title, authors, journal, year, user_id, attrank, pagerank, 3y_cc, citation_count')
            ->from('pmc_paper')
            // needed to show if already bookmarked
            ->leftJoin('users_likes', 'users_likes.paper_id = pmc_paper.internal_id AND users_likes.user_id = ' . addslashes($current_user) . ' AND showit = true')
            ->where(['internal_id' => $cited])
            ->all();
     }

     /*
        Find all citations of this pmc id
        @author: Serafeim Chatzopoulos
     */
     public static function getCitations($paper_id){

        $citations = (new \yii\db\Query())
            ->select('citing')
            ->from('citation_graph')
            ->where(['cited' => $paper_id])
            ->orderBy(['citing' => SORT_ASC]);

        $current_user = (Yii::$app->user->id ? Yii::$app->user->id : 0);

        return (new \yii\db\Query())
            ->select('internal_id, doi, title, authors, journal, year, user_id, attrank, pagerank, 3y_cc, citation_count')
            ->from('pmc_paper')
            // needed to show if already bookmarked
            ->leftJoin('users_likes', 'users_likes.paper_id = pmc_paper.internal_id AND users_likes.user_id = ' . addslashes($current_user) . ' AND showit = true')
            ->where(['internal_id' => $citations])
            ->all();
     }

    public function calculatePyramidStatisticsTotal() {
        // count all articles
        $articles_total = Article::find()->count();

        // COUNT PYRAMID STATICS WITH MySQL
        // $more_popular_total = Article::find()->where(['>=', 'attrank', $this->attrank])->count();
        // $more_influential_total = Article::find()->where(['>=', 'pagerank', $this->pagerank])->count();

        // COUNT PYRAMID STATICS WITH SOLR
        $query = Yii::$app->solr->createSelect();
        $query->createFilterQuery('pop_filter')->setQuery('popularity:[' . $this->attrank . ' TO *]');
        $query->setRows(0);
        $result = Yii::$app->solr->select($query);
        $more_popular_total = $result->getData()['response']['numFound'];

        $query = Yii::$app->solr->createSelect();
        $query->createFilterQuery('infl_filter')->setQuery('influence:[' . $this->pagerank . ' TO *]');
        $query->setRows(0);
        $result = Yii::$app->solr->select($query);
        $more_influential_total = $result->getData()['response']['numFound'];

        // calculate percentages
        $this->pop_total   = Article::getTopPercentage($more_popular_total, $articles_total);
        $this->inf_total   = Article::getTopPercentage($more_influential_total, $articles_total);

    }

    public function calculatePyramidStatisticsJournal() {

        // COUNT PYRAMID STATICS WITH MySQL
        // // count all articles in the same journal
        // $articles_in_journal = Article::find()->where(['journal' => $this->journal])->count();
        // $more_popular_in_journal = Article::find()->where(['journal' => $this->journal])->andWhere(['>=','attrank', $this->attrank])->count();
        // $more_influential_in_journal = Article::find()->where(['journal' => $this->journal])->andWhere(['>=','pagerank', $this->pagerank])->count();

        if ($this->journal) {

            $query = Yii::$app->solr->createSelect();
            $query->createFilterQuery('journal_filter')->setQuery("journal:" . $query->getHelper()->escapePhrase($this->journal));
            $query->setRows(0);
            $result = Yii::$app->solr->select($query);
            $articles_in_journal = $result->getData()['response']['numFound'];

            $query = Yii::$app->solr->createSelect();
            $query->createFilterQuery('pop_filter')->setQuery('popularity:[' . $this->attrank . ' TO *]');
            $query->createFilterQuery('journal_filter')->setQuery("journal:" . $query->getHelper()->escapePhrase($this->journal));
            $query->setRows(0);
            $result = Yii::$app->solr->select($query);
            $more_popular_in_journal = $result->getData()['response']['numFound'];

            $query = Yii::$app->solr->createSelect();
            $query->createFilterQuery('infl_filter')->setQuery('influence:[' . $this->pagerank . ' TO *]');
            $query->createFilterQuery('journal_filter')->setQuery("journal:" . $query->getHelper()->escapePhrase($this->journal));
            $query->setRows(0);
            $result = Yii::$app->solr->select($query);
            $more_influential_in_journal = $result->getData()['response']['numFound'];

            $query = Yii::$app->solr->createSelect();
            $query->createFilterQuery('imp_filter')->setQuery('impulse:[' . $this->{'3y_cc'} . ' TO *]');
            $query->createFilterQuery('journal_filter')->setQuery("journal:" . $query->getHelper()->escapePhrase($this->journal));
            $query->setRows(0);
            $result = Yii::$app->solr->select($query);
            $more_impulsive_in_journal = $result->getData()['response']['numFound'];

            $query = Yii::$app->solr->createSelect();
            $query->createFilterQuery('cc_filter')->setQuery('citation_count:[' . $this->{'citation_count'} . ' TO *]');
            $query->createFilterQuery('journal_filter')->setQuery("journal:" . $query->getHelper()->escapePhrase($this->journal));
            $query->setRows(0);
            $result = Yii::$app->solr->select($query);
            $more_cc_in_journal = $result->getData()['response']['numFound'];

            // calculate percentages
            $this->inf_journal = Article::getTopPercentage($more_influential_in_journal, $articles_in_journal);
            $this->pop_journal = Article::getTopPercentage($more_popular_in_journal, $articles_in_journal);
            $this->imp_journal = Article::getTopPercentage($more_impulsive_in_journal, $articles_in_journal);
            $this->cc_journal = Article::getTopPercentage($more_cc_in_journal, $articles_in_journal);
        }
    }

    public function calculateJournalClasses() {

        $this->calculatePyramidStatisticsJournal();

        if ($this->journal) {

            // calculate classes
            $this->inf_journal_class = SearchForm::transformPercentageToClass($this->inf_journal);
            $this->pop_journal_class = SearchForm::transformPercentageToClass($this->pop_journal);
            $this->imp_journal_class = SearchForm::transformPercentageToClass($this->imp_journal);
            $this->cc_journal_class = SearchForm::transformPercentageToClass($this->cc_journal);
        }
    }

     public function calculateCitationHistory() {

        $article_citation_history = Article::findOne($this->internal_id)->getCitationHistory()->asArray()->all();

        // if we do not have the publication year && the article has citations
        // then we remove the fist year (year = 0) from the citation history
        // and display the citation history normally, starting from the first year that has citations
        if (empty($this->year) && sizeof($article_citation_history) > 1) {
            array_shift($article_citation_history);
        }

        $article_years = array_column($article_citation_history, 'year');
        $article_citations = array_column($article_citation_history, 'cc');

        $domain_min_x = min($article_years);
        $domain_max_x = max($article_years);
        $domain_max_y = max($article_citations);

        //Fix up the citation history array of the article to be correctly displayed.
        //a. add zero cc on starting and final year
        //b. add zero cc's on missing years
        //Create an array with the range of years for the particular article
        $years_range = range($domain_min_x, $domain_max_x);
        //Years that are in the range, and DO have cc values
        $years_in_range = array();
        //Record the years that DO have values
        foreach ($article_citation_history as $entry)
        {
            $year = $entry["year"];
            array_push($years_in_range, $year);
        }
        //Find the missing years
        $years_missing = array_diff($years_range,$years_in_range);
        //Add a record with zero CC at each missing year
        foreach ($years_missing as $missing_year)
        {
            $missing_year_offset = array_search($missing_year, $years_range);
            array_splice($article_citation_history, $missing_year_offset, 0, array(array("year" => $missing_year, "cc" => 0)));
        }

        //Add first record at cc = 0 regardless of other values, to correctly close the area
        $first_record = $article_citation_history[0];
        $first_record['cc'] = 0;
        array_unshift($article_citation_history, $first_record);

        //Add final record at cc = 0 regardless of others to correctly close the line area
        array_push($article_citation_history, array('year' => $domain_max_x, 'cc' => 0));


        //Dummy array of paper citation histories. Will only contain one history
        $this->citation_history = array($this->internal_id => $article_citation_history);

        //See how many years (age) we need to compare to avg influential paper
        $paper_age = count($article_citation_history);
        //Get avg influential paper history
        $avg_exceptional_paper_history = PaperCitationHistories::find()->select(["internal_id", 'cc', "(year + $domain_min_x) as year"])->where(['internal_id' => -1])->orderBy('year ASC')->limit($paper_age-2)->asArray()->all();
        array_push($avg_exceptional_paper_history, array("year" => $avg_exceptional_paper_history[count($avg_exceptional_paper_history)-1]["year"], "cc" => 0));
        array_unshift($avg_exceptional_paper_history, array("year" => $domain_min_x, "cc" => 0));
        //Get max of avg influential paper
        $max_y_inf = max(array_column($avg_exceptional_paper_history, 'cc'));
        $domain_max_y = $max_y_inf > $domain_max_y ? $max_y_inf : $domain_max_y;


        $avg_substantial_paper_history = PaperCitationHistories::find()->select(["internal_id", 'cc', "(year + $domain_min_x) as year"])->where(['internal_id' => -10])->orderBy('year ASC')->limit($paper_age-2)->asArray()->all();
        array_push($avg_substantial_paper_history, array("year" => $avg_substantial_paper_history[count($avg_substantial_paper_history)-1]["year"], "cc" => 0));
        array_unshift($avg_substantial_paper_history, array("year" => $domain_min_x, "cc" => 0));
        //Get max of avg influential paper
        $max_y_inf = max(array_column($avg_substantial_paper_history, 'cc'));
        $domain_max_y = $max_y_inf > $domain_max_y ? $max_y_inf : $domain_max_y;
        //echo "New max = $domain_max_y<br>";
        //echo "Max inf: $max_y_inf<br>";


        //Add to examined histories
        $this->citation_history['avg_exceptional'] = $avg_exceptional_paper_history;
        $this->citation_history['avg_substantial'] = $avg_substantial_paper_history;

        // $this->citation_history = json_encode($this->citation_history);
        //print_r($this->paper_citation_histories);

        return [
            'xmin' => $domain_min_x,
            'xmax' => $domain_max_x,
            'ymax' => $domain_max_y,
        ];
     }

     public function formatDetails() {
        // authors
        if(substr($this->authors, 0, 7) == "unknown" || trim($this->authors) == "")
            $this->authors = "N/A";

        // abstract
        if (trim($this->abstract) == "") {
            $this->abstract = "N/A";

        } else if(strlen($this->abstract) > 550) {
            $this->abstract = substr($this->abstract, 0, 550) . '... <a href="https://doi.org/'.  $this->doi . '" target="_blank" class="text-custom-color">(read more)</a>';
        }
     }

     public function getTopics() {

        // get topics and their weights for topics chart
        $weights = (new \yii\db\Query())
            ->select(['topic_id AS label', 'weight AS value'])
            ->from('papers_to_topics_new')
            ->where(['paper_id' => $this->internal_id])
            ->orderBy(['weight' => SORT_DESC])
            ->all();

        // keep topic ids for second query
        $topic_ids = array_column($weights, "label");

        $colors = ["#94d194", "#bce8f1", "#f2dede", "#B0B0B0"];
        $weights_sum = 0;
        foreach ($weights as $key => $value) {
            $weights[$key]["label"] = 'Topic ' . $weights[$key]["label"];
            $weights[$key]["color"] = $colors[$key];
            $weights[$key]["value"] = doubleval($weights[$key]["value"]);
            $weights_sum += $value["value"];
        }

        array_push($weights, [ 'label' => 'Other', 'value' => (1 - $weights_sum), 'color' => $colors[3]]);


        // get topic terms and their weights for tag clouds

        $tags_arr = (new \yii\db\Query())
            ->select(['topic_id', 'token', 'weight'])
            ->from('topics_to_tokens_new')
            ->where(['topic_id' => $topic_ids])
            ->all();

        $begin_colors = ['449D44', '31708f', 'a94442'];
        $end_colors = ['94d194', 'bce8f1', 'f2dede'];
        $panel_class = ['panel-success', 'panel-info', 'panel-danger'];
        $btn_class = ['btn-success', 'btn-info', 'btn-danger'];

        $tags = [];
        foreach ($topic_ids as $index => $topic_id) {
            $tags[$topic_id]['beginColor'] = $begin_colors[$index];
            $tags[$topic_id]['endColor'] = $end_colors[$index];
            $tags[$topic_id]['panelClass'] = $panel_class[$index];
            $tags[$topic_id]['btnClass'] = $btn_class[$index];
            $tags[$topic_id]['tags'] = [];
        }

        foreach ($tags_arr as $row) {
            $tags[$row['topic_id']]['tags'][$row['token']] = [ 'weight' => $row['weight']];
        }

        return [
            'ids' => $topic_ids,
            'weights' => $weights,
            'tags' => $tags,
        ];
     }

     public function getArticlesWithTopic($topic_id) {

        $paper_ids = (new \yii\db\Query())
            ->select(['paper_id'])
            ->from('papers_to_topics_new')
            ->where(['topic_id' => $topic_id])
            ->orderBy(['weight' => SORT_DESC])
            ->limit(50)
            ->all();

        $paper_ids = array_column($paper_ids, 'paper_id');
        // print_r(count($paper_ids));

        // get paper details from the database, join with user likes
        $current_user = (Yii::$app->user->id ? Yii::$app->user->id : 0);

        return (new \yii\db\Query())
            ->select(['internal_id', 'doi', 'pmc', 'title', 'authors', 'journal', 'year', 'user_id'])
            ->from('pmc_paper')
            ->leftJoin('users_likes', 'users_likes.paper_id = pmc_paper.internal_id AND users_likes.user_id = ' . addslashes($current_user) . ' AND showit = true')
            ->leftJoin('papers_to_topics_new', 'papers_to_topics_new.paper_id = pmc_paper.internal_id')
            ->where(['in', 'internal_id', $paper_ids])
            ->andWhere(['topic_id' => $topic_id])
            ->orderBy(['weight' => SORT_DESC])
            ->all();
     }

    public function getBibtex($doi) {
        $headers = [
            'Accept: application/x-bibtex; charset=utf-8'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://doi.org/" . $doi);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $server_output = curl_exec ($ch);
        curl_close ($ch);
        return $server_output;
    }

    public function getPDFLink($doi) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.unpaywall.org/v2/" . $doi . "?email=diwis@imis.athena-innovation.gr");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $server_output = curl_exec ($ch);
        curl_close ($ch);

        $data = json_decode($server_output, true);

        $pdf_link = NULL;
        if (isset($data['best_oa_location']['url_for_pdf'])) {
            $pdf_link = $data['best_oa_location']['url_for_pdf'];

            // add pdf link in the database
            // $command = Yii::$app->db->createCommand();
            // $command->update('pmc_paper', [ 'pdf_link' => $this->pdf_link ], [ 'doi' => $doi ])->execute();

        }
        return $pdf_link;
    }

    // DEPRECATED
    // public static function getMendeleyAccessToken() {

    //     $data=
    //     [
    //         'grant_type' => 'client_credentials',
    //         'scope' => 'all',
    //         'client_id' => '7518',
    //         'client_secret' => '5QC9GGdpwqOMOwlc'
    //     ];

    //     $client = new Client(['baseUrl' => 'https://api.mendeley.com']);
    //     $response = $client->createRequest()
    //                         ->setMethod('POST')
    //                         ->addHeaders(['Content-Type'=>'application/x-www-form-urlencoded'])
    //                         ->setUrl('oauth/token')
    //                         ->setData($data)
    //                         ->send();

    //     if (!$response->getIsOk())
    //     {
    //         return NULL;
    //     }

    //     return $response->data['access_token'];
    // }

    // DEPRECATED
    // public function getMendeleyReaders($access_token, $doi) {

    //     // first request catalog_id based on doi
    //     $data = [
    //         'doi' => $doi,
    //         'access_token' => $access_token,
    //     ];

    //     $client = new Client(['baseUrl' => 'https://api.mendeley.com']);
    //     $response = $client->createRequest()
    //                         ->setMethod('GET')
    //                         ->addHeaders(['Accept' => 'application/vnd.mendeley-document-lookup.1+json'])
    //                         ->setUrl('metadata')
    //                         ->setData($data)
    //                         ->send();

    //     if (!$response->getIsOk() || !isset($response->data['catalog_id'])) {
    //         return 0;
    //     }

    //     // based on catalog_id, request catalog stats info
    //     $data = [
    //         'view' => 'stats',
    //         'access_token' => $access_token,
    //     ];

    //     $response = $client->createRequest()
    //                         ->setMethod('GET')
    //                         ->setUrl('catalog/' . $response->data['catalog_id'])
    //                         ->setData($data)
    //                         ->send();

    //     if (!$response->getIsOk()) {
    //         return 0;
    //     }

    //     return $response->data;
    // }


    public static function roundUpToNearestMagnitude($n) {

        if ($n == 0) return 1;
        $log = log10(abs($n));

        $decimalPlaces = (($log > 0)) ? (ceil($log)) : (floor($log) + 1);
        $rounded = pow(10, $decimalPlaces);
        return $rounded;

    }

    public function caclulateLikes($internal_id) {
        $this->likes = UsersLikes::getArticleLikePercentage($internal_id);
    }

    public static function getCitationsCount() {
      return (new \yii\db\Query())->from('citation_graph')->count();
    }

    public function calculateChartData() {

        if (!isset($this->pop_class))
            return;

        // overall chart data
        $overall_chart_data = ChartData::calculate($this->pop_class, $this->inf_class, $this->imp_class, $this->cc_class);
        $this->chart_data['overall'] = $overall_chart_data;

        // concept-based chart data
        $concept_chart_data = ChartData::calculateForConcepts($this->concepts);
        $this->chart_data = array_merge($this->chart_data, $concept_chart_data);
    }

    public function getPidName() {
        if (str_starts_with($this->doi, "10."))
            return "DOI";
        return "PubMed Id";
    }
    
}
