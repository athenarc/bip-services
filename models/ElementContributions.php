<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%element_contributions}}".
 *
 * @property int $id
 * @property int $element_id
 * @property string $heading_type
 * @property int|null $show_header
 * @property int|null $show_pagination
 * @property int|null $show_missing_papers
 * @property string|null $sort
 * @property int|null $top_k
 * @property int|null $page_size
 * @property int         $user_defined
 * @property int|null    $user_defined_max
 * @property string|null $prefilter_types     JSON text (array)
 * @property string|null $prefilter_accesses  JSON text (array)
 * @property string|null $compact_view        Display mode: 'full', 'compact', 'minimal'
 *
 * @property Elements $element
 */
class ElementContributions extends \yii\db\ActiveRecord
{
    public $filters_accesses = []; // '1','0',''  ('' means Unknown)
    public $filters_types    = []; // '0','1','2','3'
    public $top_k_toggle = 0;
    public $compact_view = 'full'; // Virtual property

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%element_contributions}}';
    }

    /**
     * {@inheritdoc}
     */

    public function rules()
    {
        return [
            [['element_id'], 'required'],
            [['element_id'], 'integer'],
            [['heading_type'], 'in', 'range' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']],
            [['compact_view'], 'in', 'range' => ['full', 'compact', 'minimal']],
            [['compact_view'], 'default', 'value' => 'full'],
            [['element_id'], 'exist', 'skipOnError' => true, 'targetClass' => Elements::class, 'targetAttribute' => ['element_id' => 'id']],

            [['show_header', 'show_pagination', 'show_missing_papers'], 'boolean'],
            [['sort'], 'in', 'range' => array_keys(Yii::$app->params['impact_fields'])],
            [['top_k', 'page_size'], 'integer', 'min' => 1, 'message' => 'Please enter a positive integer.'],
            [['top_k', 'page_size'], 'default', 'value' => null],

            [['user_defined'], 'boolean'],

            // validate only if user_defined is checked
            [
                ['user_defined_max'],
                'integer',
                'min' => 0,
                'when' => function ($model) { return (int)$model->user_defined === 1; },
                'whenClient' => "function () { return $('#elementcontributions-user_defined').is(':checked'); }"
            ],
            // when not user_defined, null it server-side so it’s ignored
            [['user_defined_max'], 'default', 'value' => null],

            [['filters_accesses', 'filters_types', 'compact_view'], 'safe'],
            ['filters_accesses', 'each', 'rule' => ['in', 'range' => ['', '0', '1']]],
            ['filters_types',    'each', 'rule' => ['in', 'range' => ['0','1','2','3']]],
            [['prefilter_accesses', 'prefilter_types'], 'string'],
            [['top_k_toggle'], 'boolean'],
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function attributes()
    {
        return array_merge(parent::attributes(), ['compact_view']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'element_id' => 'Element ID',
            'show_header' => 'Show header',
            'show_pagination' => 'Show pagination',
            'show_missing_papers' => 'Show missing papers',
            'sort' => 'Sort',
            'top_k' => 'Top K',
            'page_size' => 'Page size',
            'heading_type' => 'Header size',
            'compact_view' => 'Display Mode',
            'user_defined'        => 'Researcher selection',
            'user_defined_max'    => 'Max user-selected works',
            'filters_accesses' => 'Availability (default filters)',
            'filters_types'    => 'Work type (default filters)',
            'top_k_toggle' => 'Top-K',
            'compact_view' => 'Display Mode',
        ];
    }

    /**
     * Gets query for [[Element]].
     *
     * @return \yii\db\ActiveQuery
     */

    public function afterFind()
    {
        parent::afterFind();

        // Decode JSON columns to arrays (default to [])
        $acc = $this->prefilter_accesses ? json_decode($this->prefilter_accesses, true) : [];
        $typ = $this->prefilter_types    ? json_decode($this->prefilter_types, true)    : [];

        // Force string keys as we use '' / '0' / '1', etc.
        $this->filters_accesses = array_map('strval', (array)$acc);
        $this->filters_types    = array_map('strval', (array)$typ);
        $this->top_k_toggle = ($this->top_k !== null && $this->top_k !== '');
        
        // Load compact_view from database
        $this->compact_view = $this->getAttribute('compact_view') ?: 'full';
    }

    public function beforeValidate()
    {
        if (!is_array($this->filters_accesses)) $this->filters_accesses = [];
        if (!is_array($this->filters_types))    $this->filters_types    = [];

        if ((int)$this->user_defined !== 1) {
            // Clear on server if toggle is off
            $this->user_defined_max = null;
        }
        if (!$this->top_k_toggle) {
            $this->top_k = null;
        }
        return parent::beforeValidate();
    }

    public function beforeSave($insert)
    {
        // Persist the virtuals into JSON columns
        $this->prefilter_accesses = json_encode(array_values($this->filters_accesses ?: []), JSON_UNESCAPED_UNICODE);
        $this->prefilter_types    = json_encode(array_values($this->filters_types    ?: []), JSON_UNESCAPED_UNICODE);
        
        // Save compact_view to database
        if (isset($this->compact_view)) {
            $this->setAttribute('compact_view', $this->compact_view);
        }

        return parent::beforeSave($insert);
    }

    public function getElement()
    {
        return $this->hasOne(Elements::class, ['id' => 'element_id']);
    }

    /**
     * Retrieves the configuration for a contribution list based on element_id.
     *
     * @param int $element_id
     * @return ElementContributions|null
     */
    public static function getConfigContributions($element_id) {
        $model = self::find()->where(['element_id' => $element_id])->one();
        if (!$model) return [];

        return [
            'heading_type'     => $model->heading_type,
            'show_header'      => $model->show_header,
            'show_pagination'  => $model->show_pagination,
            'show_missing_papers' => $model->show_missing_papers,
            'sort'             => $model->sort,
            'top_k'            => $model->top_k,
            'page_size'        => $model->page_size,
            'user_defined'     => $model->user_defined,
            'user_defined_max' => $model->user_defined ? $model->user_defined_max : null,
            'compact_view'     => $model->compact_view ?: 'full',
            'filters' => [
                'accesses' => $model->prefilter_accesses ? (json_decode($model->prefilter_accesses, true) ?: []) : [],
                'types'    => $model->prefilter_types    ? (json_decode($model->prefilter_types, true)    ?: []) : [],
            ],
        ];
    }

}
