<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use vova07\imperavi\Widget;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\moules\blog\models\Blog */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="blog-form">

    <?php $form = ActiveForm::begin([
            'options' => ['enctype'=>'multipart/form-data']
        ]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'text')->widget(Widget::className(), [
        'settings' => [
            'lang' => 'ru',
            'minHeight' => 200,
            'plugins' => [
                'clips',
                'fullscreen',
            ],
            'clips' => [
                ['Lorem ipsum...', 'Lorem...'],
                ['red', '<span class="label-red">red</span>'],
                ['green', '<span class="label-green">green</span>'],
                ['blue', '<span class="label-blue">blue</span>'],
            ],
            'imageUpload' => \yii\helpers\Url::to(['/site/save-redactor-image', 'sub' => 'blog']),
        ],
    ]);
    ?>
    <div class="row">
        <?= $form->field($model, 'file', ['options'=>['class'=>'col-md-6']])->widget(\kartik\file\FileInput::classname(), [
            'options' => ['accept' => 'image/*'],
            'pluginOptions' => [
                    'showCaption' => false,
                    'showRemove' => false,
                    'showUpload' => false,
                    'browseClass' => 'btn btn-primary btn-block',
                    'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
                    'browseLabel' =>  'Select Photo'
                ],
            ]);?>


        <?= $form->field($model, 'image', ['options'=>['class'=>'col-md-6']])->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'url',['options'=>['class'=>'col-md-6']]  )->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'status_id', ['options'=>['class'=>'col-md-6']])->dropDownList(\fogmart\blog\models\Blog::STATUS_LIST) ?>

        <?= $form->field($model, 'sort',['options'=>['class'=>'col-md-6']])->textInput() ?>
    </div>

    <?= $form->field($model, 'tags_arr')->widget(Select2::classname(), [
            'data' =>
                \yii\helpers\ArrayHelper::map(\fogmart\blog\models\Tag::find()->all(), 'id', 'name'),
            'language' => 'ru',
            'options' => ['placeholder' => 'Выбрать тег', 'multiple'=> true],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]);
    ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <?= \kartik\file\FileInput::widget( [
        'name' => 'ImageManager[attachment]',
        'options' => [
                'accept' => 'image/*',
                'multiple'=>true
        ],
        'pluginOptions' => [
            'uploadUrl' => \yii\helpers\Url::to(['/site/save-image']),
            'deleteUrl' => \yii\helpers\Url::to(['/blog/delete-image']),
            'initialPreview'=>$model->imagesLinks,
            'initialPreviewConfig' => $model->imageLinkData,
            'initialPreviewAsData'=>true,
            'overriteInitial' =>false,
            'uploadExtraData' => [
                'ImageManager[class]' => $model->formName(),
                'ImageManager[itm_id]' => $model->id
            ],
            'maxFileCount' => 10
        ],
        'pluginEvents'=>[
                'filesorted' => new \yii\web\JsExpression('function(event, params){
                    $.post("'.\yii\helpers\Url::toRoute(['/blog/sort-image','id'=>$model->id]).'", {sort:params});
                }')
        ]
    ]);?>


</div>
