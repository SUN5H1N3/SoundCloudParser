<?php

namespace app\components\soundcloud\parsers;

use app\components\extended\ExtendedDateInterval;
use app\components\soundcloud\SoundCloudUrlManager;
use app\models\Artist;
use app\models\Track;
use DOMElement;
use phpQuery;
use phpQueryObject;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\httpclient\Client;
use yii\httpclient\Response;
use function pq;

class SoundCloudParserHtml extends SoundCloudParser
{
    public Client $client;

    public SoundCloudUrlManager $urlManager;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->client ??= new Client();
        $this->urlManager ??= new SoundCloudUrlManager();
    }

    /**
     * @param string $slug
     * @return Artist
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function parseArtist(string $slug): Artist
    {
        $url = $this->urlManager->artist($slug);
        $response = $this->sendRequest($url);

        $htmlDoc = phpQuery::newDocumentHTML($response->content);
        $scripts = $htmlDoc->find('script');

        $artist = $this->getExistedArtist($slug) ?? new Artist();

        foreach ($scripts as $scriptDOMElem) {
            $scriptElem = pq($scriptDOMElem);
            $scriptContent = $scriptElem->text();

            if (stripos($scriptContent, 'window.__sc_hydration') !== false) {
                if (!$this->parseArtistScript($artist, $scriptContent)) {
                    return $artist;
                }

                $this->log('Parsed artist attributes:', $artist->getDirtyAttributes());
                return $artist;
            }
        }

        $this->addParseError('Script for parsing not found.');
        return $artist;
    }

    /**
     * @param string $artistSlug
     * @param int|null $limit
     * @return Track[]
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function parseTracks(string $artistSlug, int $limit = NULL): array
    {
        $url = $this->urlManager->artistPopularTracks($artistSlug);
        $response = $this->sendRequest($url);
        $tracks = $this->getParsedTracks($response->content, $artistSlug, $limit);

        if (!$tracks) {
            $this->addParseError('No tracks found from html.');
        }
        return $tracks;
    }

    private function parseArtistScript(Artist $artist, string $scriptContent): bool
    {
        $data = $this->extractDataFromArtistScript($scriptContent);
        if (!$data) {
            $this->addParseError('No parsed data from script.');
            return false;
        }

        $artist->followers_count = $data['followers_count'] ?? $artist->followers_count;
        $artist->followings_count = $data['followings_count'] ?? $artist->followings_count;
        $artist->full_name = $data['full_name'] ?? $artist->full_name;
        $artist->city = $data['city'] ?? $artist->city;
        $artist->country_code = $data['country_code'] ?? $artist->country_code;
        $artist->first_name = $data['first_name'] ?? $artist->first_name;
        $artist->soundcloud_id = $data['id'] ?? $artist->soundcloud_id;
        $artist->last_name = $data['last_name'] ?? $artist->last_name;
        $artist->likes_count = $data['likes_count'] ?? $artist->likes_count;
        $artist->tracks_count = $data['track_count'] ?? $artist->tracks_count;
        $artist->username = $data['username'] ?? $artist->username;
        $artist->verified = $data['verified'] ?? $artist->verified;
        $artist->slug = $data['permalink'] ?? $artist->slug;

        return true;
    }

    /**
     * @param string $scriptContent
     * @return array
     */
    private function extractDataFromArtistScript(string $scriptContent): array
    {
        $scriptContent = str_replace('window.__sc_hydration = ', '', $scriptContent);
        $scriptContent = rtrim($scriptContent, ';');
        $decodedContent = Json::decode($scriptContent);

        foreach ($decodedContent as $contentPart) {
            if (isset($contentPart['hydratable']) && $contentPart['hydratable'] === 'user') {
                return $contentPart['data'] ?? [];
            }
        }
        return [];
    }

    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    private function getParsedTracks(string $responseData, string $artistSlug, int $limit = NULL): array
    {
        $htmlDoc = phpQuery::newDocumentHTML($responseData);
        $tracksElems = $htmlDoc->find("[itemprop='track']")->elements;
        if ($limit !== NULL) {
            $tracksElems = array_slice($tracksElems, 0, $limit);
        }
        $slugs = $this->parseTrackSlugs($tracksElems, $artistSlug);
        if (!$slugs) {
            return [];
        }
        $tracksElems = array_combine($slugs, $tracksElems);
        $existedTracks = $this->getExistedTracks($slugs);

        $tracks = [];
        foreach ($tracksElems as $slug => $trackElem) {
            $trackPhpQuery = pq($trackElem);
            $track = $existedTracks[$slug] ?? new Track(['slug' => $slug]);
            $tracks[] = $this->parseTrack($track, $trackPhpQuery, $artistSlug);
        }
        return $this->addArtistIdIfExisted($tracks, $artistSlug);
    }

    /**
     * @param Track[] $tracks
     * @return Track[]
     */
    private function addArtistIdIfExisted(array $tracks, string $artistSlug): array
    {
        $artist = $this->getExistedArtist($artistSlug);
        if (!$artist) {
            return $tracks;
        }
        foreach ($tracks as $track) {
            $track->artist_id = $artist->id;
        }
        return $tracks;
    }

    /**
     * @param DOMElement[] $tracksElems
     * @param string $artistSlug
     * @return array
     */
    private function parseTrackSlugs(array $tracksElems, string $artistSlug): array
    {
        $slugs = [];
        foreach ($tracksElems as $trackElem) {
            $trackPhpQuery = pq($trackElem);
            $nameElem = $trackPhpQuery->find("[href^='/$artistSlug/']");
            $slugs[] = $this->getSlug($nameElem->attr('href'));
        }
        return $slugs;
    }

    /**
     * @param Track $track
     * @param phpQueryObject $pq
     * @param string $artistSlug
     * @return Track
     * @throws Exception
     * @throws InvalidConfigException
     */
    private function parseTrack(Track $track, phpQueryObject $pq, string $artistSlug): Track
    {
        $performerElem = $pq->find("[href='/$artistSlug']");
        $nameElem = $pq->find("[href^='/$artistSlug/']");
        $publicationElem = $pq->find('time');
        $durationElem = $pq->find("[itemprop='duration']");
        $genreElem = $pq->find("[itemprop='genre']");

        $track->name = $this->getTrackNameByTitle($nameElem->text()) ?? $track->name;
        $track->performer = $performerElem->text() ?? $track->performer;
        $track->artist_slug = $artistSlug ?? $track->artist_slug;
        $track->publication_date = Yii::$app->formatter->asDate($publicationElem->text(), 'php:Y-m-d H:i:s') ?? $track->publication_date;
        $track->duration = (new ExtendedDateInterval($durationElem->attr('content')))->toMilliseconds() ?? $track->duration;
        $track->genre = $genreElem->attr('content') ?? $track->genre;

        $this->log('Parsed track attributes:', $track->getDirtyAttributes());

        return $track;
    }

    private function getSlug(string $url): string
    {
        return trim(pathinfo($url, PATHINFO_FILENAME), '\\/');
    }

    /**
     * @param string $url
     * @return Response
     * @throws Exception
     * @throws InvalidConfigException
     */
    protected function sendRequest(string $url): Response
    {
        return $this->client->createRequest()
            ->setUrl($url)
            ->setMethod('GET')
            ->setFormat(Client::FORMAT_XML)
            ->send();
    }

    public static function getId(): string
    {
        return 'html';
    }
}