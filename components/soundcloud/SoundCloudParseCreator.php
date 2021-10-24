<?php

namespace app\components\soundcloud;

use app\components\soundcloud\parsers\HtmlSoundCloudParser;
use app\components\soundcloud\parsers\SoundCloudParser;
use yii\base\Model;

class SoundCloudParseCreator extends Model
{
    public static function create(string $className = NULL): SoundCloudParser
    {
        if ($className && is_subclass_of($className, SoundCloudParser::class)) {
            return new $className();
        }
        return new HtmlSoundCloudParser();
    }
}