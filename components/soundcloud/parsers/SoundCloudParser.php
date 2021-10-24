<?php

namespace app\components\soundcloud\parsers;

use app\models\Track;
use yii\base\Exception;
use yii\base\Model;

abstract class SoundCloudParser extends Model
{
    abstract public function parseArtist(string $slug): void;

    /**
     * @param string $artistSlug
     * @param int|null $limit
     * @return Track[]
     * @throws Exception
     */
    abstract public function parseTracks(string $artistSlug, int $limit = NULL): array;
}