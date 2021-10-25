<?php

namespace app\components\soundcloud\parsers;

use app\models\Artist;

class StableSoundCloudParser extends SoundCloudParser
{
    protected SoundCloudParserHtml $parserHtml;
    protected SoundCloudParserApi $parserApi;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->parserApi = new SoundCloudParserApi($config);
        $this->parserHtml = new SoundCloudParserHtml($config);
    }

    /**
     * @inheritDoc
     */
    public function parseArtist(string $slug): Artist
    {
        $artist = $this->parserHtml->parseArtist($slug);
        $this->addErrors([self::PARSE_ERROR => $this->parserHtml->parseErrors]);
        return $artist;
    }

    /**
     * @inheritDoc
     */
    public function parseTracks(string $artistSlug, int $limit = NULL): array
    {
        $tracks = $this->parserApi->parseTracks($artistSlug, $limit);
        $this->addErrors([self::PARSE_ERROR => $this->parserApi->parseErrors]);
        return $tracks;
    }

    public static function getId(): string
    {
        return 'stable';
    }
}