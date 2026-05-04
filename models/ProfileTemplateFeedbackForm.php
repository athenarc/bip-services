<?php

namespace app\models;

use Yii;
use yii\base\Model;

class ProfileTemplateFeedbackForm extends Model
{
    public $template_id;
    public $profile_orcid;
    public $message;

    public function rules()
    {
        return [
            [['template_id', 'profile_orcid', 'message'], 'required'],
            [['template_id'], 'integer'],
            [['profile_orcid'], 'string', 'max' => 19],
            [['message'], 'string', 'max' => 2000],
        ];
    }

    public function save()
    {
        if (! $this->validate()) {
            return false;
        }

        $user_id = Yii::$app->user->id;
        if (! $user_id) {
            $this->addError('message', 'You must be logged in to submit feedback.');
            return false;
        }

        $researcher = Researcher::findOne(['orcid' => $this->profile_orcid]);
        if (! $researcher) {
            $this->addError('profile_orcid', 'Invalid profile ORCID.');
            return false;
        }

        $template = Templates::findOne(['id' => $this->template_id]);
        if (! $template) {
            $this->addError('template_id', 'Invalid template.');
            return false;
        }
        if (! $template->isHidden()) {
            $this->addError('template_id', 'Feedback can only be submitted for hidden templates.');
            return false;
        }

        $feedback = new ProfileTemplateFeedback();
        $feedback->template_id = (int) $this->template_id;
        $feedback->profile_orcid = $this->profile_orcid;
        $feedback->reporter_user_id = (int) $user_id;
        $feedback->message = $this->message;
        $feedback->status = ProfileTemplateFeedback::STATUS_PENDING;

        if (! $feedback->save()) {
            $this->addError('message', 'Unable to save feedback.');
            return false;
        }

        return true;
    }
}
