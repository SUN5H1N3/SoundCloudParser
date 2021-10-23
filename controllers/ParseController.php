<?php

namespace app\controllers;

use app\components\soundcloud\parsers\HtmlSoundCloudParser;
use yii\web\Controller;

class ParseController extends Controller
{
    public function actionArtist(string $slug)
    {
        $parser = new HtmlSoundCloudParser();
        $parser->parseArtist($slug);
    }
}