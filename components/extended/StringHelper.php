<?php

namespace app\components\extended;

use yii\helpers\BaseStringHelper;

class StringHelper extends BaseStringHelper
{
    public static function implode(array $array, $separator = '', bool $onlyNoEmpty = true): string
    {
        if ($onlyNoEmpty) {
            $array = array_filter($array);
        }
        return implode($separator, $array);
    }
}