<?php

namespace app\models;

use Yii;
use app\models\Tags;

class TagsToPapers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tags_to_papers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'paper_id', 'tag_id'], 'required'],
            [['user_id', 'paper_id', 'tag_id'], 'integer'],
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
            'tag id'  => 'Tag ID'
        ];
    }

    public static function addTag($user_id, $paper_id, $tag_name) {
        
        $tag = Tags::find()->where(['name' => $tag_name])->one();

        // insert new tag
        if (!$tag) {
            $tag = new Tags();
            $tag->name = $tag_name;
            $tag->used = 1;
            $tag->save();
        
        // update tag counter
        } else {
            $tag->used += 1;
            $tag->update();
        }

        // save connection between paper and tag
        $record = new TagsToPapers();
        $record->user_id = $user_id;
        $record->paper_id = $paper_id;
        $record->tag_id = $tag->id;
        $record->save();

        return $tag->id;
    }

    public static function removeTag($user_id, $paper_id, $tag_name) {
        $tag = Tags::find()->where(['name' => $tag_name])->one();

        // update tag counter
        if($tag) {
            
            // delete connection between paper and tag
            $record = TagsToPapers::find()->where(['user_id' => $user_id, 'paper_id' => $paper_id, 'tag_id' => $tag->id])->one();
            if ($record) {
                $record->delete();
            }

            $tag->used -= 1;

            // delete tag if no other connection uses it
            if ($tag->used == 0) {
                $tag->delete();
            } else { 
                $tag->update();
            }

            return $tag->id;
        } else {
            // tag does not exist
            throw new \yii\base\Exception;
        }
    }
}
