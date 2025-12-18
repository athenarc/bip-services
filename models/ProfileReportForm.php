<?php

namespace app\models;

use Yii;
use yii\base\Model;

class ProfileReportForm extends Model {
    public $reported_orcid;

    public $reason;

    public $description;

    private $_saved_report_id = null;

    public function rules() {
        return [
            [['reported_orcid', 'reason'], 'required'],
            [['reported_orcid'], 'string', 'max' => 19],
            [['reason'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 1000],
        ];
    }

    public function attributeLabels() {
        return [
            'reported_orcid' => 'Profile ORCID',
            'reason' => 'Reason for Reporting',
            'description' => 'Additional Details (optional)',
        ];
    }

    public function getSavedReportId() {
        return $this->_saved_report_id;
    }

    public function save() {
        if (! $this->validate()) {
            return false;
        }

        $reporter_user_id = Yii::$app->user->id;

        if (! $reporter_user_id) {
            $this->addError('reported_orcid', 'You must be logged in to report a profile.');

            return false;
        }

        // Prevent users from reporting their own profile
        $researcher = Researcher::findOne(['orcid' => $this->reported_orcid]);

        if ($researcher && $researcher->user_id === $reporter_user_id) {
            $this->addError('reported_orcid', 'You cannot report your own profile.');

            return false;
        }

        $report = ProfileReport::create(
            $this->reported_orcid,
            $reporter_user_id,
            $this->reason,
            $this->description
        );

        if ($report !== null) {
            $this->_saved_report_id = $report->id;

            return true;
        }

        return false;
    }
}
