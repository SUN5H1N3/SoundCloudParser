<?php

namespace app\components\soundcloud;

use yii\base\Model;

class SoundCloudUrlManager extends Model
{
    public string $baseUrl = 'https://soundcloud.com';

    public function artist(string $slug): string
    {
        return $this->baseUrl . "/$slug";
    }

    public function artistPopularTracks(string $artistSlug): string
    {
        return $this->baseUrl . "/$artistSlug/popular-tracks";
    }

    public function artistTracks(string $artistSlug): string
    {
        return $this->baseUrl . "/$artistSlug/tracks";
    }
}