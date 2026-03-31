<?php

/* @var $this yii\web\View */
/* @var $model app\models\BlogPost */

use yii\helpers\Html;

$this->title = 'Edit blog post: ' . $model->title;

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1 col-xs-12">
            <div class="help-text-left">
                <h2><?= Html::encode($this->title) ?></h2>
                <hr>
                <div class="list-group list-group-shadow">
                    <div class="list-group-item">
                        <?= $this->render('_form', ['model' => $model]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
