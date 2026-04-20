<?php

namespace app\components;

use yii\helpers\HtmlPurifier;

class BlogContentSanitizer {
    public static function purify(string $html): string {
        return HtmlPurifier::process($html, self::config());
    }

    public static function config(): array {
        return [
            'HTML.Allowed' => 'iframe[src|width|height|frameborder],'
                . 'p[style],div[style],span[style],'
                . 'h1[style],h2[style],h3[style],h4[style],h5[style],h6[style],'
                . 'blockquote[style],pre[style],code,'
                . 'strong,b,i,em,u,sub,sup,br,'
                . 'ul[style],ol[style],li[style],'
                . 'a[href|target|rel],'
                . 'table[style],thead,tbody,tfoot,tr,th[style],td[style]',
            'CSS.AllowedProperties' => 'text-align,text-decoration,font-size,font-family,margin-left,padding-left,text-indent,list-style-type,width,height,border,border-width,border-style,border-color,border-collapse,background-color',
            'HTML.SafeIframe' => true,
            'URI.SafeIframeRegexp' => '%^(http://|https://|//)(www.youtube.com/embed/|player.vimeo.com/video/|api.soundcloud.com/tracks/|www.youtube-nocookie.com/embed/)%',
        ];
    }
}
