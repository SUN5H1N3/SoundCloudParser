<?php

namespace app\models;

use app\components\extended\ExtendedActiveRecord;
use app\models\queries\ArtistQuery;
use app\models\queries\TrackQuery;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "track".
 *
 * @property int $id
 * @property int|null $soundcloud_id
 * @property int|null $artist_id
 * @property string|null $name
 * @property string|null $performer
 * @property string|null $slug
 * @property string|null $artist_slug
 * @property string|null $genre
 * @property int|null $duration ms
 * @property string|null $publication_date
 * @property int|null $playback_count
 * @property int|null $comment_count
 *
 * @property Artist $artist
 */
class Track extends ExtendedActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'track';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['publication_date'], 'filter', 'filter' => static function ($date) {
                return Yii::$app->formatter->asDate($date, 'php:Y-m-d H:i:s');
            }],
            [['publication_date'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['playback_count', 'comment_count', 'duration', 'soundcloud_id', 'artist_id'], 'integer'],
            [['artist_id'], 'exist', 'targetClass' => Artist::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
            [['name', 'performer'], 'trim'],
            [['name', 'performer', 'slug', 'genre', 'artist_slug'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'performer' => Yii::t('app', 'Full Name'),
            'duration' => Yii::t('app', 'Duration'),
            'publication_date' => Yii::t('app', 'Publication Date'),
            'playback_count' => Yii::t('app', 'Playback Count'),
            'comment_count' => Yii::t('app', 'Comment Count'),
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
     * {@inheritdoc}
     * @return TrackQuery the active query used by this AR class.
     */
    public static function find(): TrackQuery
    {
        return new TrackQuery(static::class);
    }
}
