<?php

namespace app\components\soundcloud\parsers;

use app\components\soundcloud\SoundCloudUrlManager;
use app\models\Track;
use DOMElement;
use phpQuery;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\httpclient\Exception;
use yii\httpclient\Response;
use function pq;

class HtmlSoundCloudParser extends SoundCloudParser
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
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function parseArtist(string $slug): void
    {
        $url = $this->urlManager->artist($slug);
        $response = $this->sendRequest($url);

        $htmlDoc = phpQuery::newDocumentHTML($response->content);
    }

    public function parseTracks(string $artistSlug, int $limit = NULL): array
    {
        $url = $this->urlManager->artistPopularTracks($artistSlug);
        $response = $this->sendRequest($url);

        $htmlDoc = phpQuery::newDocumentHTML($response->content);
        $tracksHtml = $htmlDoc->find("[itemprop='track']");

        $tracks = [];
        $tracksCount = 0;

        /** @var DOMElement $trackElem */
        foreach ($tracksHtml as $trackElem) {
            $pq = pq($trackElem);
            $performerElem = $pq->find("[href='/$artistSlug']");
            $nameElem = $pq->find("[href^='/$artistSlug/']");
            $publicationElem = $pq->find('time');

            $track = new Track();
            $track->name = $this->getTrackName($nameElem->text());
            $track->performer = $performerElem->text();
            $track->slug = $nameElem->attr('href');
            $track->publication_date = $publicationElem->text();





            $tracks[] = $track;



            var_dump($track->validate());
            var_dump($track->attributes);
            if (++$tracksCount >= $limit) {
                break;
            }
        }

        return $tracks;
    }

    private function getTrackName(string $name): string
    {
        if (preg_match('/(?<performer>.+)\s[\-–—]\s(?<name>.+)$/u', $name, $matches)) {
            return $matches['name'];
        }
        return $name;
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
}