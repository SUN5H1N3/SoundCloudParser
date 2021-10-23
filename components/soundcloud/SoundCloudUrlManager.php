<?php

namespace app\components\soundcloud;

use yii\base\Model;

class SoundCloudUrlManager extends Model
{
    public string $baseUrl = 'https://soundcloud.com';

    public function artist(string $slug): string
    {
        return $this->baseUrl . '/' . $slug;
    }
}