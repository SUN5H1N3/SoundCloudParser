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
        $this->addParseError('No available API methods for parse artist. Use other parser ([' . StableSoundCloudParser::getId() . '] recommended).');

        return new Artist();
    }

    public function parseTracks(string $artistSlug, int $limit = NULL): array
    {
        $artist = $this->getArtistBySlug($artistSlug);

        if (!$artist->soundcloud_id) {
            $this->addParseError('Cannot find sound_cloud_id for this slug(' . $artistSlug . ').');
            return [];
        }

        $tracksData = $this->getTracksData($artist->soundcloud_id, $limit);
        return $this->getTrackModels($tracksData, $artist);
    }

    /**
     * @throws \yii\base\Exception
     * @throws InvalidConfigException
     */
    private function getArtistBySlug(string $artistSlug): Artist
    {
        $dbArtist = Artist::find()
            ->select('soundcloud_id')
            ->where(['slug' => $artistSlug])
            ->one();

        if ($dbArtist && $dbArtist->soundcloud_id) {
            return $dbArtist;
        }

        $htmlParser = new StableSoundCloudParser();
        return $htmlParser->parseArtist($artistSlug);
    }

    /**
     * @param Track[] $tracksData
     * @param Artist $artist
     * @return array
     * @throws InvalidConfigException
     */
    private function getTrackModels(array $tracksData, Artist $artist): array
    {
        if (!$tracksData) {
            $this->addParseError('No tracks data from api.');
            return [];
        }

        $tracks = [];
        Yii::$app->formatter->nullDisplay = NULL;

        $existedTracks = $this->getExistedTracks(array_map(static fn($item) => $item['permalink'], $tracksData));

        foreach ($tracksData as $trackData) {
            $slug = $trackData['permalink'];
            $track = $existedTracks[$slug] ?? new Track();
            $track->slug = $slug;
            $track->comment_count = $trackData['comment_count'] ?? $track->comment_count;
            $track->artist_id = $artist->id ?? $track->artist_id;
            $track->duration = $trackData['duration'] ?? $track->duration;
            $track->genre = $trackData['genre'] ?? $track->genre;
            $track->soundcloud_id = $trackData['id'] ?? $track->soundcloud_id;
            $publicationDate = $tracksData['release_date'] ?? $tracksData['display_date'] ?? $trackData['created_at'];
            $track->publication_date = Yii::$app->formatter->asDate($publicationDate, 'php:Y-m-d H:i:s') ?? $track->publication_date;
            $track->playback_count = $trackData['playback_count'] ?? $track->playback_count;
            $track->performer = $trackData['user']['username'] ?? $trackData['publisher_metadata']['artist'] ?? $track->performer;
            $track->artist_slug = $trackData['user']['permalink'] ?? $track->artist_slug;
            $track->name = $this->getTrackNameByTitle($trackData['title']) ?? $track->name;
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
            return $collection;
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