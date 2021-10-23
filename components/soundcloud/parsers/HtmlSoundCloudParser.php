<?php

namespace app\components\soundcloud\parsers;

use app\components\soundcloud\parsers\SoundCloudParser;
use DOMElement;
use phpQuery;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\httpclient\Exception;
use function pq;

class HtmlSoundCloudParser extends SoundCloudParser
{

    /**
     * @param string $slug
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function parseArtist(string $slug): void
    {
        $url = $this->urlManager->artist($slug);
        $response = $this->client->createRequest()
            ->setUrl($url)
            ->setMethod('GET')
            ->setFormat(Client::FORMAT_XML)
            ->send();


        $htmlDoc = phpQuery::newDocumentHTML($response->content);
        $tracksHtml = $htmlDoc->find("[itemprop='track']");

        /** @var DOMElement $trackElem */
        foreach ($tracksHtml as $trackElem) {
            $pq = pq($trackElem);
            $trackArtistElem = $pq->find("[href='/$slug']");
            $trackNameElem = $pq->find("[href^='/$slug/']");
            echo $trackArtistElem->text() . '<br>';
            echo $trackNameElem->text() . '<br>';
            echo $trackNameElem->attr('href') . '<br>' . '<br>';

        }
    }
}