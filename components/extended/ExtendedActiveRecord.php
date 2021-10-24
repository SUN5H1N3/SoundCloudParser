<?php

namespace app\components\extended;

use yii\db\ActiveRecord;

abstract class ExtendedActiveRecord extends ActiveRecord
{
    /**
     * {@inheritDoc}
     * Now have types
     * @return array
     */
    public function rules(): array
    {
        return parent::rules();
    }

    /**
     * {@inheritDoc}
     * Now have types
     * @return array
     */
    public function attributeLabels(): array
    {
        return parent::attributeLabels();
    }

    /**
     * {@inheritDoc}
     * Now have types
     * @return string
     */
    public static function tableName(): string
    {
        return parent::tableName();
    }
}