<?php

namespace app\components\soundcloud\parsers;

use app\components\soundcloud\SoundCloudUrlManager;
use yii\base\Model;
use yii\httpclient\Client;

abstract class SoundCloudParser extends Model
{
    public SoundCloudUrlManager $urlManager;

    public Client $client;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->urlManager ??= new SoundCloudUrlManager();
        $this->client ??= new Client();
    }

    abstract public function parseArtist(string $slug): void;
}