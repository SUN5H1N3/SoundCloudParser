<?php

namespace app\models;

use app\components\extended\ExtendedActiveRecord;
use app\models\queries\ArtistQuery;
use app\models\queries\TrackQuery;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "track_artist".
 *
 * @property int|null $artist_id
 * @property int|null $track_id
 *
 * @property Artist $artist
 * @property Track $track
 */
class TrackArtist extends ExtendedActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'track_artist';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['artist_id', 'track_id'], 'integer'],
            [['track_id', 'artist_id'], 'unique', 'targetAttribute' => ['track_id', 'artist_id']],
            [['artist_id'], 'exist', 'skipOnError' => true, 'targetClass' => Artist::class, 'targetAttribute' => ['artist_id' => 'id']],
            [['track_id'], 'exist', 'skipOnError' => true, 'targetClass' => Track::class, 'targetAttribute' => ['track_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'artist_id' => Yii::t('app', 'Artist ID'),
            'track_id' => Yii::t('app', 'Track ID'),
        ];
    }

    /**
     * Gets query for [[Artist]].
     *
     * @return ArtistQuery
     */
    public function getArtist(): ActiveQuery
    {
        return $this->hasOne(Artist::class, ['id' => 'artist_id']);
    }

    /**
     * Gets query for [[Track]].
     *
     * @return TrackQuery
     */
    public function getTrack(): ActiveQuery
    {
        return $this->hasOne(Track::class, ['id' => 'track_id']);
    }
}
