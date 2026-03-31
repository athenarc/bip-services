<?php

namespace app\components;

use yii\helpers\HtmlPurifier;

class BlogContentSanitizer {
    public static function purify(string $html): string {
        return HtmlPurifier::process($html, self::config());
    }

    public static function config(): array {
        return [
            'HTML.Allowed' => 'iframe[src|width|height|frameborder],p[style],strong,b,i,em,u,sub,sup,br,ul[style],ol[style],li[style],a[href|target|rel]',
            'CSS.AllowedProperties' => 'margin-left,padding-left,text-indent,list-style-type',
            'HTML.SafeIframe' => true,
            'URI.SafeIframeRegexp' => '%^(http://|https://|//)(www.youtube.com/embed/|player.vimeo.com/video/|api.soundcloud.com/tracks/|www.youtube-nocookie.com/embed/)%',
        ];
    }
}
