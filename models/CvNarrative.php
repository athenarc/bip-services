<?php

namespace app\models;

use Yii;

class CvNarrative extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cv_narratives';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'user_id', 'description', 'papers'], 'required'],
            [['user_id'], 'integer'],
            [['title', 'description', 'papers'], 'string'],
            [['title'], 'trim'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'  => 'CV Narrative ID',
            'title' => 'Title',
            'description' => 'Description',
            'user_id' => 'Owner',
            'papers' => 'Paper',
        ];
    }

    public static function updateCvNarrative($user_id, $is_public, $cv_narrative_id) {

        if (empty($cv_narrative_id)){
            // update all user cv narratives
            CvNarrative::updateAll(['is_public' => $is_public], ['user_id' => $user_id]);

        }
        else {

            // update existing narrative
            $cv_narrative = CvNarrative::find()->where(['id' => $cv_narrative_id, 'user_id' => $user_id])->one();
            if (!$cv_narrative) {
                throw new \yii\base\Exception;
            }

            $cv_narrative->is_public = $is_public;
            $cv_narrative->save();

        }

    }

    public static function CountPublicCvNarratives ($cv_narratives) {

        // count the number of public cv narratives
        $public_cv_narratives_count = 0;
        foreach ($cv_narratives as $cv_narrative) {
            if ($cv_narrative['is_public'] == 1) {
                $public_cv_narratives_count += 1;
            }
        }
        return $public_cv_narratives_count;
    }
}
