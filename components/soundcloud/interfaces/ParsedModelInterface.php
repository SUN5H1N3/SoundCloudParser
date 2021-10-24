<?php

namespace app\components\soundcloud\interfaces;

use app\components\extended\interfaces\ExtendedActiveRecordInterface;

interface ParsedModelInterface extends ExtendedActiveRecordInterface
{
    public static function getParseModel(): self;
}