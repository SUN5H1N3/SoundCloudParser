<?php

namespace app\components\soundcloud\api\mobi;

use app\components\Api;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\httpclient\Exception;
use yii\httpclient\Response;

class SoundCloudApi extends Api
{
    private const TRACK_MIN_LIMIT = 9;

    /**
     * @param int $userId
     * @param array|null $parameters
     * @return Response
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function tracks(int $userId, array $parameters = NULL): Response
    {
        if (isset($parameters['limit']) && ($parameters['limit'] < self::TRACK_MIN_LIMIT)) {
            $parameters['limit'] = self::TRACK_MIN_LIMIT;
        }

        $url = $this->buildUrl(['users', $userId, 'tracks'], $parameters);
        return $this->httpClient->createRequest()
            ->setUrl($url)
            ->setMethod('GET')
            ->send();
    }

    /**
     * @param string|array $path
     * @param array|null $query
     * @return string
     */
    private function buildUrl($path, array $query = NULL): string
    {
        $url = $this->baseUrl . '/';
        $url .= is_array($path) ? implode('/', $path) : $path;

        $defaultQuery = [
            'client_id' => $this->getClientId(),
            'app_version' => $this->getAppVersion(),
        ];
        $query = $query ? ArrayHelper::merge($defaultQuery, $query) : $defaultQuery;
        $url .= '?' . http_build_query($query);

        return $url;
    }

    public function getBaseUrl(): string
    {
        return 'https://api-mobi.soundcloud.com';
    }

    /**
     * For now static client_id, because the algorithm for issuing it is unknown
     * @return string
     */
    public function getClientId(): string
    {
        return 'iZIs9mchVcX5lhVRyQGGAYlNPVldzAoX';
    }

    public function getAppVersion(): int
    {
        return 1634813470;
    }
}