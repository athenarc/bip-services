<?php

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

class TinyColorAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        ['js/third-party/tinycolor.js', 'position' => View::POS_HEAD],
    ];
}

