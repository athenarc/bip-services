<?php

/* 
 * @Hlias: Class to talk with pmc_paper table and get the pmc - doi mapping
 */

namespace app\models;

use yii\db\ActiveRecord;

class DoiToPmc extends ActiveRecord
{
    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return 'pmc_paper';
    }
    
    public static function getPmc($doi)
    {
        $pmc_array = DoiToPmc::find()->select('pmc')->where(['doi' => $doi])->asArray()->one();
        return $pmc_array["pmc"];
    }
}
