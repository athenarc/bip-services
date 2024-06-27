<?php

namespace app\models;

use Yii;

class Involvement extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'involvement_to_papers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'paper_id'], 'required'],
            [['user_id', 'paper_id', 'involvement'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'paper_id' => 'Paper ID',
            'involvement'  => 'Involvement'
        ];
    }

    public static function updateInvolvement($user_id, $paper_id, $involvement_id, $is_selected) {

        $found = Involvement::find()->where(['user_id' => $user_id, 'paper_id' => $paper_id, 'involvement' => $involvement_id])->one();
        if(!$found && $is_selected == "true") {
            $new_involvement = new Involvement();
            $new_involvement->user_id = $user_id;
            $new_involvement->paper_id = $paper_id;
            $new_involvement->involvement = $involvement_id;
            $new_involvement->save();    
        } elseif ($found && $is_selected == "false") {
            $found->delete();
        }
    }

    public static function getInvolvement($rows, $user_id) {

        $paper_involvements = (new \yii\db\Query())
            ->select("paper_id, involvement")
            ->from('involvement_to_papers')
            ->where(['user_id' => $user_id])
            ->all();

        // Group by paper_id
        $paper_id_to_involvements = \yii\helpers\ArrayHelper::map($paper_involvements,'involvement',[],'paper_id');

        foreach ($rows["papers"] as $paper => $info){
            $id = $rows["papers"][$paper]['internal_id'];
            $involvement = (array_key_exists($id, $paper_id_to_involvements))? array_keys($paper_id_to_involvements[$id]) : [];
            // Create involvement key to input array
            $rows["papers"][$paper]["involvement"] = $involvement;
        }
        
        return $rows;
    }
}
