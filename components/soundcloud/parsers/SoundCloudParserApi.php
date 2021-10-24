<?php

namespace app\components\soundcloud\parsers;

use app\components\extended\Url;
use app\components\soundcloud\api\mobi\SoundCloudApi;
use app\models\Artist;
use app\models\Track;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use yii\httpclient\Exception;

class SoundCloudParserApi extends SoundCloudParser
{
    public SoundCloudApi $api;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->api = new SoundCloudApi();
    }

    public function parseArtist(string $slug): Artist
    {
        $this->_isSuccessLastParse = false;

        $message = 'No available API methods for parse artist. Use other parser ([stable] recommended).';
        $this->log($message);
        $this->addError('api', $message);

        return new Artist();
    }

    public function parseTracks(string $artistSlug, int $limit = NULL): array
    {
        $this->_isSuccessLastParse = false;

        $artistId = $this->getArtistIdBySlug($artistSlug);
        $tracksData = $this->getTracksData($artistId, $limit);
        return $this->getTrackModels($tracksData);
    }

    private function getArtistIdBySlug(string $artistSlug): int
    {
        $dbArtist = Artist::find()
            ->select('soundcloud_id')
            ->where(['slug' => $artistSlug])
            ->one();

        if ($dbArtist && $dbArtist->soundcloud_id) {
            return $dbArtist->soundcloud_id;
        }

        $htmlParser = new SoundCloudParserHtml();
        return $htmlParser->parseArtist($artistSlug)->soundcloud_id;
    }

    /**
     * @param Track[] $tracksData
     * @return array
     * @throws InvalidConfigException
     */
    private function getTrackModels(array $tracksData): array
    {
        if (!$tracksData) {
            return [];
        }

        $this->_isSuccessLastParse = true;
        $tracks = [];
        Yii::$app->formatter->nullDisplay = NULL;

        foreach ($tracksData as $trackData) {
            $track = new Track();
            $track->comment_count = $trackData['comment_count'] ?? $track->comment_count;
            $track->duration = $trackData['duration'] ?? $track->duration;
            $track->genre = $trackData['genre'] ?? $track->genre;
            $track->soundcloud_id = $trackData['id'] ?? $track->soundcloud_id;
            $track->publication_date = Yii::$app->formatter->asDate($trackData['release_date'], 'php:Y-m-d H:i:s') ?? $track->publication_date;
            $track->slug = $trackData['permalink'] ?? $track->slug;
            $track->playback_count = $trackData['playback_count'] ?? $track->playback_count;
            $track->performer = $trackData['publisher_metadata']['artist'] ?? $track->performer;
            $track->artist_slug = $trackData['user']['permalink'] ?? $track->artist_slug;
            $track->name = $this->getNameByTitle($trackData['title']) ?? $track->name;
            $tracks[] = $track;

            $this->log('Parsed track attributes:', $track->getDirtyAttributes());
        }
        return $tracks;
    }

    /**
     * @param int $userId
     * @param int|null $limit
     * @return array
     * @throws InvalidConfigException
     * @throws Exception
     */
    private function getTracksData(int $userId, int $limit = NULL): array
    {
        $response = $this->api->tracks($userId, [
            'limit' => $limit,
        ]);
        $data = $this->getTracksDataRecursive($response->data, $limit);
//        var_dump($data);exit;
        return $data ? array_slice($data, 0, $limit) : [];
    }

    /**
     * @param array $prevResponseData
     * @param int $recursiveLimit
     * @return array
     * @throws Exception
     * @throws InvalidConfigException
     */
    private function getTracksDataRecursive(array $prevResponseData, int $collectionLimit = NULL, int $recursiveLimit = 10, array $collection = []): array
    {
        $collection = ArrayHelper::merge($collection, $prevResponseData['collection'] ?? []);
        $collectionCount = count($collection);
        if ($recursiveLimit <= 0 || $collectionCount >= $collectionLimit) {
            return $collection;
        }
        $nextHref = $prevResponseData['next_href'] ?? NULL;
        if (!$nextHref) {
            return [];
        }
        $nextHref = Url::addQueryParams($nextHref, ['client_id' => $this->api->getClientId()]);
        $this->log("Recursive request ($collectionCount received)", $nextHref);
        $response = $this->api->httpClient->createRequest()
            ->setUrl($nextHref)
            ->setFormat(Client::FORMAT_JSON)
            ->send();
        return $this->getTracksDataRecursive($response->data, $collectionLimit, $recursiveLimit - 1, $collection);
    }

    public static function getId(): string
    {
        return 'api';
    }
}