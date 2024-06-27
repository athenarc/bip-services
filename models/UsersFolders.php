<?php

namespace app\models;

use Yii;

class UsersFolders extends \yii\db\ActiveRecord
{
	
	 public static function tableName()
    {
        return 'users_folders';
    }
	
	 public function rules()
    {
        return [
            [['name','user_id'], 'required'],
            ['name','unique', 'targetAttribute' => ['name', 'user_id'], 'comboNotUnique' => 'Folder name already exists. Please choose another folder name.'], //combination of user_id and (folder)name must be unique 
			 //Rule required to recognise attribute
			[['id', 'user_id'], 'integer'],
			[['name'], 'string', 'max' => 30],
        ];
    }
    
	/**
	 * ATTENTION! WE USE comboNotUnique INSTEAD OF message TO GET A CUSTOM ERROR MESSAGE
	 * FOR UNIQUE MULTIPLE FIELDS. IN NEXT VERSIONS OF YII THIS WILL BE FIXED...
	 */
	
	 public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Folder name',
            'user_id' => 'User id',
        ];
    }

    public static function removeBookmarks($folder_id) {
        $command = Yii::$app->db->createCommand();
        $command->update('users_likes', [ 'folder_id' => NULL ], [ 'folder_id' => $folder_id ])->execute();
    }
}
	