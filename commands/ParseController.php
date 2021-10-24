<?php

namespace app\commands;

use app\components\soundcloud\SoundCloudParseCreator;
use yii\base\Exception;
use yii\console\Controller;

class ParseController extends Controller
{
    /**
     * @throws Exception
     */
    public function actionTracks(string $slug, int $limit)
    {
        $parser = SoundCloudParseCreator::create();
        $tracks = $parser->parseTracks($slug, $limit);
    }
}