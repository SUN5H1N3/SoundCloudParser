<?php

namespace app\controllers;

use app\components\extended\VarDumper;
use app\components\soundcloud\SoundCloudParseCreator;
use yii\base\Exception;
use yii\web\Controller;

class ParseController extends Controller
{
    /**
     * @throws Exception
     */
    public function actionArtist(string $slug)
    {
        $parser = SoundCloudParseCreator::create();
        $tracks = $parser->parseTracks($slug);

        VarDumper::dump($tracks);exit;
    }
}