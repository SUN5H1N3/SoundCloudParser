<?php

namespace app\components;

use yii\base\Model;
use yii\httpclient\Client;

/**
 * @property-read $baseUrl
 */
abstract class Api extends Model
{
    public Client $httpClient;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->httpClient = new Client();
    }

    abstract public function getBaseUrl(): string;
}