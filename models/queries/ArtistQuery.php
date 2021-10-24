<?php

namespace app\models\queries;

/**
 * This is the ActiveQuery class for [[\app\models\Artist]].
 *
 * @see \app\models\Artist
 */
class ArtistQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \app\models\Artist[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \app\models\Artist|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
