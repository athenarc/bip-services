<?php

/*
 * Widget for displaying impact icons:
 *
 * @params:
 * popularity_class: A,B,C,D,E depending on popularity score
 * influence_class: A,B,C,D,E depending on influence score
 * impulse_class: A,B,C,D,E depending on impulse score
 *
 * (First version: July 2021)
 */

# ---------------------------------------------------------------------------- #

/*
 * Define the namespace of the widget
 */
namespace app\components;

/*
 * Includes
 */
use yii\base\Widget;
use Yii;
use yii\helpers\Url;
use yii\helpers\HTML;

/*
 * The widget class
 */
class ImpactIcons extends Widget
{
    /*
     * Widget properties
     */

    //  Necessary inputs
    public $popularity_score;
    public $popularity_class;
    public $influence_score;
    public $influence_class;
    public $impulse_score;
    public $impulse_class;
    public $impact_indicators;
    public $cc_score;
    public $cc_class;
    // Necessary input only for details view
    // options = [mode=>, header=> (optional), css_classes=> (optional)]
    public $options;

    // Properties calculated inside the class
    public $has_scores_classes;
    public $popularity_class_perc;
    public $popularity_class_message_short;
    public $popularity_class_message_ext;
    public $popularity_popover_content;
    public $influence_class_perc;
    public $influence_class_message_short;
    public $influence_class_message_ext;
    public $influence_popover_content;
    public $impulse_class_perc;
    public $impulse_class_message_short;
    public $impulse_class_message_ext;
    public $impulse_popover_content;
    public $cc_class_perc;
    public $cc_class_message_short;
    public $cc_class_message_ext;
    public $cc_popover_content;

    /*
     * Widget initialisation a.k.a. setting widget properties
     */
    public function init()
    {
        parent::init();
        // score : number(including 0) or null
        $has_scores = (isset($this->popularity_score) && isset($this->influence_score) && isset($this->impulse_score) && isset($this->cc_score)) ? true : false;
        // class : 'A','B',..,'F' or null
        $has_classes = (isset($this->popularity_class) && isset($this->influence_class) && isset($this->impulse_class) && isset($this->cc_class)) ? true : false;
        $this->has_scores_classes = $has_scores && $has_classes;

        if ($this->has_scores_classes){
            $this->popularity_class_perc = $this->getClassTitle($this->popularity_class, 'title');
            $this->influence_class_perc = $this->getClassTitle($this->influence_class,'title');
            $this->impulse_class_perc = $this->getClassTitle($this->impulse_class, 'title');
            $this->cc_class_perc = $this->getClassTitle($this->cc_class, 'title');

            $this->popularity_class_message_short = $this->getClassTitle($this->popularity_class,'short') .', in terms of popularity';
            $this->influence_class_message_short = $this->getClassTitle($this->influence_class, 'short') .', in terms of influence';
            $this->impulse_class_message_short = $this->getClassTitle($this->impulse_class, 'short') .', in terms of impulse';
            $this->cc_class_message_short = $this->getClassTitle($this->cc_class, 'short') .', in terms of citation count';

            $this->popularity_class_message_ext = $this->getClassTitle($this->popularity_class,'ext') .', in terms of popularity';
            $this->influence_class_message_ext = $this->getClassTitle($this->influence_class, 'ext') .', in terms of influence';
            $this->impulse_class_message_ext = $this->getClassTitle($this->impulse_class, 'ext') .', in terms of impulse (momentum directly after publication)';
            $this->cc_class_message_ext = $this->getClassTitle($this->cc_class, 'ext') .', in terms of citation count';

        }
    }

    private function getClassTitle($class, $message){
        // $message : 'title', 'short', 'ext'
        if ($message === 'short'){
            $prefix = 'In ';
        } elseif ($message === 'ext'){
            $prefix = 'Being in ';
        } elseif ($message === 'title')
            $prefix = '';

        $class_info =  Yii::$app->params['impact_classes'][$class];
        return ($message !== 'ext' && !empty($class_info['impact_icon_name'])) ? $class_info['impact_icon_name'] : $prefix . $class_info['name'];

    }

    private function getPopoverContent($message_ext, $indicator, $score){
        $content = "<div><em>{$message_ext}</em></div>";
        $content .= "<div><span class='green-bip'>Intuition:</span> " . Html::encode($this->impact_indicators[$indicator]) . " <a target='_blank' class='green-bip' href='"  . Url::toRoute(['site/indicators', '#' => $indicator]) .  "'><i class='fa fa-external-link-square' aria-hidden='true'></i></a></div>";
        $content .= "<div><span class='green-bip'>Score:</span> ";
        $content .= ($indicator === 'Impulse' or $indicator === 'Influence-alt' ) ? $score : sprintf('%.2e', $score);
        $content .= "</div>";

        return $content;
    }
    /*
     * Running the widget a.k.a. rendering results
     */
    public function run()
    {

        $array_var = [
            'has_scores_classes' => $this->has_scores_classes
        ];

        if ($this->has_scores_classes){

            $this->popularity_popover_content = $this->getPopoverContent($this->popularity_class_message_ext, "Popularity", $this->popularity_score);
            $this->influence_popover_content = $this->getPopoverContent($this->influence_class_message_ext, "Influence", $this->influence_score);
            $this->impulse_popover_content = $this->getPopoverContent($this->impulse_class_message_ext, "Impulse", $this->impulse_score);
            $this->cc_popover_content = $this->getPopoverContent($this->cc_class_message_ext, "Influence-alt", $this->cc_score);

            $array_scores_classes = [
                'impact_indicators' => $this->impact_indicators,
                'popularity_score' => $this->popularity_score,
                'popularity_class' => $this->popularity_class,
                'popularity_class_perc' => $this->popularity_class_perc,
                'popularity_class_message_short' => $this->popularity_class_message_short,
                'popularity_class_message_ext' => $this->popularity_class_message_ext,
                'popularity_popover_content' => $this->popularity_popover_content,
                'influence_score' => $this->influence_score,
                'influence_class' => $this->influence_class,
                'influence_class_perc' => $this->influence_class_perc,
                'influence_class_message_short' => $this->influence_class_message_short,
                'influence_class_message_ext' => $this->influence_class_message_ext,
                'influence_popover_content' => $this->influence_popover_content,
                'impulse_score' => $this->impulse_score,
                'impulse_class' => $this->impulse_class,
                'impulse_class_perc' => $this->impulse_class_perc,
                'impulse_class_message_short' => $this->impulse_class_message_short,
                'impulse_class_message_ext' => $this->impulse_class_message_ext,
                'impulse_popover_content' => $this->impulse_popover_content,
                'cc_score' => $this->cc_score,
                'cc_class' => $this->cc_class,
                'cc_class_perc' => $this->cc_class_perc,
                'cc_class_message_short' => $this->cc_class_message_short,
                'cc_class_message_ext' => $this->cc_class_message_ext,
                'cc_popover_content' => $this->cc_popover_content,
            ];

        $array_var = array_merge($array_var, $array_scores_classes);

        }

        $array_options = [
            'options' => $this->options
        ];


        if (isset($this->options) && isset($this->options['mode']) && $this->options['mode'] == "detailed") {
            return $this->render('detailed_impact_icons', array_merge($array_var, $array_options));
        }


        return $this->render('impact_icons', $array_var);
    }

}

?>