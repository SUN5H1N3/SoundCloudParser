<?php

namespace app\components\extended;

use yii\helpers\BaseUrl;

class Url extends BaseUrl
{
    public static function addQueryParams(string $url, array $params): string
    {
        return $url . '&' . http_build_query($params);
    }
}