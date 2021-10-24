<?php

namespace app\components\extended\interfaces;

use yii\db\ActiveRecordInterface;

interface ExtendedActiveRecordInterface extends ActiveRecordInterface
{
    public function getActiveAttributes(): array;
}