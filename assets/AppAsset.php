<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'css/radarChart.css',
        'css/third-party/fontawesome-free-6.7.1-web/css/all.min.css',
        'css/template/green-black.css',
        'https://fonts.googleapis.com/css?family=Open+Sans:400,300', //required by radarChart
        'https://fonts.googleapis.com/css?family=Raleway', //required by radarChart
        'https://fonts.googleapis.com/css2?family=Nunito:wght@350;700',
        'https://fonts.googleapis.com/css2?family=Courgette&family=Montserrat:wght@300;400',
        // 'css/progress-circle-normalize.css',
        // 'css/progress-circle.css',
        'css/bootsnippCheck.css',
        'css/lineChart.css'
    ];
    
    public $js = [
        ['js/radarChart.js',"position"=>View::POS_BEGIN,"charset"=>"utf-8"], //at the body begining
        ['js/third-party/d3/d3.min.js',"position"=>View::POS_BEGIN,"charset"=>"utf-8"],
        // ['js/progress-circle.js',"position"=>View::POS_BEGIN,"charset"=>"utf-8"],
        ['js/like_unlike.js', "position" => View::POS_BEGIN, "charset"=>"utf-8"], //For like/unlike action on hearts
        // ['js/warnMobile.js', "position" => View::POS_BEGIN, "charset"=>"utf-8"], 
        ['js/popover.js', "position" => View::POS_END, "charset"=>"utf-8"],


        //Xgraph function - MOVE these to the view that requires them
        //['js/jquery.scrollTo-1.4.3.1-min.js', 'position' => View::POS_BEGIN, 'charset'=>'utf-8'],
        //['js/mxClient.js',"position"=>View::POS_BEGIN,"charset"=>"utf-8"],
        //['js/init_xgraph.js',"position"=>View::POS_BEGIN,"charset"=>"utf-8"],
        //['js/mx_graph_functions.js',"position"=>View::POS_BEGIN,"charset"=>"utf-8"],
        //['js/process_graphviz_response.js',"position"=>View::POS_BEGIN,"charset"=>"utf-8"],

        //['js/likeButtonDisable.js',"position"=>View::POS_END,"charset"=>"utf-8"],
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\jui\JuiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
