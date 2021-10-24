<?php

namespace app\components\extended;

use DateInterval;
use yii\base\Exception;

class ExtendedDateInterval extends DateInterval
{
    /**
     * @param $duration
     * @throws Exception
     */
    public function __construct($duration)
    {
        try {
            parent::__construct($duration);
        } catch (\Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
        }
    }

    public function toSeconds(): int
    {
        return ($this->y * 365 * 24 * 60 * 60) +
            ($this->m * 30 * 24 * 60 * 60) +
            ($this->d * 24 * 60 * 60) +
            ($this->h * 60 * 60) +
            ($this->i * 60) +
            $this->s;
    }

    public function toMilliseconds(): int
    {
        return $this->toSeconds() * 1000 + $this->f / 1000;
    }
}