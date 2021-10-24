<?php

namespace app\models;

use app\components\extended\ExtendedActiveRecord;
use app\models\queries\ArtistQuery;
use app\models\queries\TrackQuery;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "artist".
 *
 * @property int $id
 * @property int|null $soundcloud_id
 * @property string $slug
 * @property string|null $username
 * @property string|null $full_name
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $city
 * @property string|null $country_code
 * @property bool|null $verified
 * @property int|null $likes_count
 * @property int|null $followers_count
 * @property int|null $followings_count
 * @property int|null $tracks_count
 *
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
            [['soundcloud_id', 'likes_count', 'followers_count', 'followings_count', 'tracks_count'], 'integer'],
            [['verified'], 'boolean'],
            [['slug'], 'required'],
            [['slug', 'username', 'full_name', 'first_name', 'last_name', 'city', 'country_code'], 'string', 'max' => 255],
            [['slug'], 'unique'],
            [['soundcloud_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'soundcloud_id' => Yii::t('app', 'Soundcloud ID'),
            'slug' => Yii::t('app', 'Slug'),
            'username' => Yii::t('app', 'Username'),
            'full_name' => Yii::t('app', 'Full Name'),
            'first_name' => Yii::t('app', 'First Name'),
            'last_name' => Yii::t('app', 'Last Name'),
            'city' => Yii::t('app', 'City'),
            'country_code' => Yii::t('app', 'Country Code'),
            'verified' => Yii::t('app', 'Verified'),
            'likes_count' => Yii::t('app', 'Likes Count'),
            'followers_count' => Yii::t('app', 'Followers Count'),
            'followings_count' => Yii::t('app', 'Followings Count'),
            'tracks_count' => Yii::t('app', 'Tracks Count'),
        ];
    }

    /**
     * Gets query for [[Tracks]].
     *
     * @return TrackQuery
     */
    public function getTracks(): ActiveQuery
    {
        return $this->hasMany(Track::class, ['id' => 'track_id']);
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
