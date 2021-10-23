<?php

namespace app\commands;

use app\components\soundcloud\parsers\HtmlSoundCloudParser;
use yii\console\Controller;

class ParseController extends Controller
{
    public function actionArtist(string $slug)
    {
        $parser = new HtmlSoundCloudParser();
        $parser->parseArtist($slug);
    }
}