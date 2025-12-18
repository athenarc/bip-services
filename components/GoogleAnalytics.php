<?php

namespace app\components;

use Yii;
use yii\helpers\Html;

/*
 * Yii Helper for adding google analytics to the website
 *
 * @author Kostis Zagganas (First Version: September 2018)
 */
class GoogleAnalytics {
    /*
     * Helper function for adding google analytics on the site
     */
    public static function checkAndAdd() {
        // do not include google analytics on test server
        if (gethostname() == 'andrea') {
            return;
        }

        $analyticsFile = '@web/js/third-party/google_analytics/analyticsScript.js';

        $cookies = Yii::$app->request->cookies;

        if (! isset($_COOKIE['bipAnalyticsOptOut'])) {
            return Html::jsFile($analyticsFile);
        }
    }

    /*
     * Helper function that returns the state of the analytics on the website.
     */
    public static function getState() {
        $analyticsFile = '@web/js/third-party/google_analytics/analyticsScript.js';

        $cookies = Yii::$app->request->cookies;

        if (isset($_COOKIE['bipAnalyticsOptOut'])) {
            return 'Off';
        }

        return 'On';
    }

    public static function getStateEnable() {
        $analyticsFile = '@web/js/third-party/google_analytics/analyticsScript.js';

        $cookies = Yii::$app->request->cookies;

        if (isset($_COOKIE['bipAnalyticsOptOut'])) {
            return 'enable';
        }

        return 'disable';
    }
}
