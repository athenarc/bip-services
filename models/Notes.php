<?php

namespace app\models;

use Yii;

class Notes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notes_to_papers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'paper_id'], 'required'],
            [['user_id', 'paper_id'], 'integer'],
            [['notes'], 'string'],
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
            'notes'  => 'Notes'
        ];
    }

    public static function updateNotes($user_id, $paper_id, $notes_value) {

        $found = Notes::find()->where(['user_id' => $user_id, 'paper_id' => $paper_id])->exists();
        if(!$found) {
            $new_note = new Notes();
            $new_note->user_id = $user_id;
            $new_note->paper_id = $paper_id;
            $new_note->notes = $notes_value;
            $new_note->save();
        }

        $note = Notes::find()->where(['user_id' => $user_id, 'paper_id' => $paper_id])->one();

        if (empty($notes_value)) {
            $note->delete();
        } else {
            $note["notes"] = $notes_value;
            $note->update();            
        }

    }

    public static function loadNote($user_id, $paper_id) {
        return (new \yii\db\Query())
            ->select('pmc_paper.*, notes_to_papers.*, users_likes.*, GROUP_CONCAT(tags.name ORDER BY tags_to_papers.timestamp ASC) AS tags')
            ->from('pmc_paper')
            ->leftJoin('tags_to_papers', 'pmc_paper.internal_id = tags_to_papers.paper_id 
                    AND tags_to_papers.user_id = ' . $user_id)        
            ->leftJoin('tags', 'tags.id = tags_to_papers.tag_id')                            
            ->leftJoin('notes_to_papers', 'pmc_paper.internal_id = notes_to_papers.paper_id 
                        AND notes_to_papers.user_id = ' . $user_id)
            ->leftJoin('users_likes', 'pmc_paper.internal_id = users_likes.paper_id 
                        AND users_likes.user_id = ' . $user_id . ' AND showit = true')             
            ->where(['internal_id' => $paper_id])
            ->one();
    }
}
