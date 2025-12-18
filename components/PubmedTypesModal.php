<?php

namespace app\components;

use Yii;
use yii\base\Widget;

class PubmedTypesModal extends Widget {
    public $modalId;

    public $checkboxClass;

    public $checkboxIdPrefix;

    public $applyButtonId;

    public $selectedTypes = [];

    public function run() {
        return $this->render('pubmed_types_modal', [
            'modalId' => $this->modalId,
            'checkboxClass' => $this->checkboxClass,
            'checkboxIdPrefix' => $this->checkboxIdPrefix,
            'applyButtonId' => $this->applyButtonId,
            'selectedTypes' => (array) $this->selectedTypes,
            'pubmed_types_fields' => Yii::$app->params['pubmed_types_fields'],
        ]);
    }
}
