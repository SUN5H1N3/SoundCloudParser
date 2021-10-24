<?php

namespace app\components\soundcloud\parsers;

use app\components\extended\StringHelper;
use app\models\Artist;
use app\models\Track;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\Inflector;

/**
 * @property-read bool $isSuccessLastParse
 */
abstract class SoundCloudParser extends Model
{
    public bool $enableLiveLogs = false;

    private array $_logs = [];

    protected bool $_isSuccessLastParse = false;

    public function getIsSuccessLastParse(): bool
    {
        return $this->_isSuccessLastParse;
    }

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

    protected function getNameByTitle(string $title): string
    {
        if (preg_match('/(?<performer>.+)\s[\-–—]\s(?<name>.+)$/u', $title, $matches)) {
            return $matches['name'];
        }
        return $title;
    }

    public static function getId(): string
    {
        return Inflector::camel2id(StringHelper::basename(static::class));
    }
}