<?php

namespace app\models;

use Yii;

class ResponsibleAcadAge extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'responsible_acad_age';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orcid', 'start_date', 'end_date', 'description'], 'required'],
            [['id'], 'integer'],
            [['orcid', 'description'], 'string'],
            [['start_date', 'end_date'], 'date','format' => 'yyyy-MM-dd' ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Rag ID',
            'orcid' => 'Orcid',
            'start_date' => 'Start Date',
            'end_date'  => 'End Date',
            'description'  => 'Description',
        ];
    }

    public static function updateRag($user_orcid, $from_date, $to_date, $description) {

        $found = ResponsibleAcadAge::find()->where(['orcid' => $user_orcid, 'start_date' => $from_date, 'end_date' => $to_date])->exists();
        $saved_id = null;
        if(!$found) {
            $new_rag = new ResponsibleAcadAge();
            $new_rag->orcid = $user_orcid;
            $new_rag->start_date = $from_date;
            $new_rag->end_date = $to_date;
            $new_rag->description = $description;
            $new_rag->save();    

            $saved_rag = ResponsibleAcadAge::find()->where(['orcid' => $user_orcid, 'start_date' => $from_date, 'end_date' => $to_date])->one();
            $saved_row['id'] = $saved_rag->id;
            $saved_row['from_date'] = $saved_rag->start_date;
            $saved_row['to_date'] = $saved_rag->end_date;
            $saved_row['description'] = $saved_rag->description;

        } 

        return [
            "found" => $found,
            "saved_row" => $saved_row
        ];

    }

    public static function removeRag($rag_id) {

        $old_rag = ResponsibleAcadAge::find()->where(['id' => $rag_id])->one();
        if($old_rag) {
            $old_rag->delete();    
        } 
    }

    public static function get_responsible_academic_age_data($user_orcid) {

		$rag_data = (new \yii\db\Query())
			->select('*')
			->from('responsible_acad_age')
			->where(['orcid' => $user_orcid])
			->all();
        
        return $rag_data;
    }
}