<?php

namespace app\models;

use app\components\extended\ExtendedActiveRecord;
use app\models\queries\ArtistQuery;
use app\models\queries\TrackQuery;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "artist".
 *
 * @property int $id
 * @property string $slug
 * @property string|null $nickname
 * @property string|null $public_name
 * @property string|null $public_location
 * @property string|null $status
 * @property int|null $followers_count
 * @property int|null $following_count
 * @property int|null $tracks_count
 *
 * @property TrackArtist[] $trackArtists
 * @property Track[] $tracks
 */
class Artist extends ExtendedActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'artist';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['slug'], 'required'],
            [['followers_count', 'following_count', 'tracks_count'], 'integer'],
            [['slug', 'nickname', 'public_name', 'public_location', 'status'], 'string', 'max' => 255],
            [['slug'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'slug' => Yii::t('app', 'Slug'),
            'nickname' => Yii::t('app', 'Nickname'),
            'public_name' => Yii::t('app', 'Public Name'),
            'public_location' => Yii::t('app', 'Public Location'),
            'status' => Yii::t('app', 'Status'),
            'followers_count' => Yii::t('app', 'Followers Count'),
            'following_count' => Yii::t('app', 'Following Count'),
            'tracks_count' => Yii::t('app', 'Tracks Count'),
        ];
    }

    /**
     * Gets query for [[TrackArtists]].
     *
     * @return ActiveQuery
     */
    public function getTrackArtists(): ActiveQuery
    {
        return $this->hasMany(TrackArtist::class, ['artist_id' => 'id']);
    }

    /**
     * Gets query for [[Tracks]].
     *
     * @return TrackQuery
     * @throws InvalidConfigException
     */
    public function getTracks(): ActiveQuery
    {
        return $this->hasMany(Track::class, ['id' => 'track_id'])->viaTable('track_artist', ['artist_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return ArtistQuery the active query used by this AR class.
     */
    public static function find(): ArtistQuery
    {
        return new ArtistQuery(static::class);
    }
}
