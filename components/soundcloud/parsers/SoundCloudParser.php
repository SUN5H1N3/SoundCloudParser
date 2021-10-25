<?php

namespace app\components\soundcloud\parsers;

use app\components\extended\StringHelper;
use app\models\Artist;
use app\models\Track;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\Inflector;

/**
 * @property-read array $parseErrors
 */
abstract class SoundCloudParser extends Model
{
    public const REGEX_TACK_TITLE = '/(?<performer>.+)\s[\-–—]\s(?<name>.+)$/u';

    public const PARSE_ERROR = 'parse_error';

    public bool $enableLiveLogs = false;

    private array $_logs = [];

    /**
     * @param string $slug
     * @return Artist
     * @throws Exception
     */
    abstract public function parseArtist(string $slug): Artist;

    /**
     * @param string $artistSlug
     * @param int|null $limit
     * @return Track[]
     * @throws Exception
     */
    abstract public function parseTracks(string $artistSlug, int $limit = NULL): array;

    /**
     * @return string
     */
    public static function getId(): string
    {
        return Inflector::camel2id(StringHelper::basename(static::class));
    }

    /**
     * @param string $message
     */
    protected function addParseError(string $message): void
    {
        $this->addError(self::PARSE_ERROR, $message);
        $this->log($message);
    }

    protected function getParseErrors(): array
    {
        return $this->getErrors(self::PARSE_ERROR);
    }

    /**
     * @return array
     */
    public function getLogs(): array
    {
        return $this->_logs;
    }

    /**
     * @param string|null $message
     * @param null $additionalInfo
     */
    public function log(string $message = NULL, $additionalInfo = NULL): void
    {
        $separator = is_string($additionalInfo) ? ': ' : PHP_EOL;
        $additionalInfo = is_string($additionalInfo) ? $additionalInfo : print_r($additionalInfo, true);

        $log = StringHelper::implode([$message, $additionalInfo], $separator);
        $this->_logs[] = $log;

        if ($this->enableLiveLogs) {
            echo $log . PHP_EOL;
        }
    }

    /**
     * @param string $title
     * @return string
     */
    protected function getTrackNameByTitle(string $title): string
    {
        if (preg_match(self::REGEX_TACK_TITLE, $title, $matches)) {
            return $matches['name'];
        }
        return $title;
    }

    protected function getExistedArtist(string $slug): ?Artist
    {
        return Artist::findOne(['slug' => $slug]);
    }

    protected function getExistedTracks(array $slugs): array
    {
        return Track::find()
            ->where(['slug' => $slugs])
            ->indexBy('slug')
            ->all();
    }
}