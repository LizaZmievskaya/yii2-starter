<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = Yii::t('user', 'Password recovery');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-recovery">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Please fill out the following fields to change your password:</p>

    <?= $this->render('/partial/change-password-form', [
        'model' => $model,
    ]); ?>

</div>
