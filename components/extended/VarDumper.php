<?php

namespace app\components\extended;

use yii\helpers\BaseVarDumper;

class VarDumper extends BaseVarDumper
{
    public static function dump($var, $depth = 10, $highlight = true): void
    {
        parent::dump($var, $depth, $highlight);
    }
}