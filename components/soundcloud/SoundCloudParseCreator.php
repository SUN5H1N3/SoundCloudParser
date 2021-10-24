<?php

namespace app\components\soundcloud;

use app\components\soundcloud\parsers\SoundCloudParser;
use app\components\soundcloud\parsers\SoundCloudParserApi;
use app\components\soundcloud\parsers\SoundCloudParserHtml;
use app\components\soundcloud\parsers\StableSoundCloudParser;
use yii\base\Model;

class SoundCloudParseCreator extends Model
{
    public static function parsers(): array
    {
        return [
            SoundCloudParserApi::getId() => SoundCloudParserApi::class,
            SoundCloudParserHtml::getId() => SoundCloudParserHtml::class,
            StableSoundCloudParser::getId() => StableSoundCloudParser::class,
        ];
    }

    public static function create(string $id = NULL, array $config = []): SoundCloudParser
    {
        if ($id !== NULL) {
            $className = static::parsers()[$id] ?? NULL;
            if ($className && is_subclass_of($className, SoundCloudParser::class)) {
                return new $className($config);
            }
        }
        return new StableSoundCloudParser($config);
    }
}