<?php

/* @var $this yii\web\View */

use yii\helpers\Markdown;

$this->title = 'Sound Cloud Parser';
echo Markdown::process(file_get_contents(__DIR__ . '/../../README.md'));
?>

