<?php

use yii\db\Migration;

/**
 * Class m211024_011157_create_soundcloud_tables
 */
class m211024_011157_create_soundcloud_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable(
            '{{%artist}}',
            [
                'id' => $this->primaryKey(),
                'slug' => $this->string()->unique()->notNull(),
                'nickname' => $this->string(),
                'public_name' => $this->string(),
                'public_location' => $this->string(),
                'status' => $this->string()->check("status IN ('verified', 'not_verified')"),
                'followers_count' => $this->integer(),
                'following_count' => $this->integer(),
                'tracks_count' => $this->integer(),
            ],
        );
        $this->createTable(
            '{{%track}}',
            [
                'id' => $this->primaryKey(),
                'name' => $this->string(),
                'performer' => $this->string(),
                'slug' => $this->string(),
                'duration' => $this->integer(),
                'publication_date' => $this->dateTime(),
                'playback_count' => $this->integer(),
                'comment_count' => $this->integer(),
            ]
        );
        $this->createTable(
            '{{%track_artist}}',
            [
                'artist_id' => $this->integer(),
                'track_id' => $this->integer()
            ]
        );
        $this->addForeignKey(
            '{{%fk-track_artist-artist_id}}',
            '{{%track_artist}}',
            '[[artist_id]]',
            '{{%artist}}',
            '[[id]]'
        );
        $this->addForeignKey(
            '{{%fk-track_artist-track_id}}',
            '{{%track_artist}}',
            '[[track_id]]',
            '{{%track}}',
            '[[id]]'
        );
        $this->createIndex(
            '{{%idx-unique-track_artist-track_id-artist_id}}',
            '{{%track_artist}}',
            ['track_id', 'artist_id'],
            true
        );
//        $this->createTable(
//            '{{%artist_web_profile}}',
//            [
//                'id' => $this->primaryKey(),
//                'site' => $this->string(),
//                'url' => $this->string(),
//            ],
//        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropForeignKey(
            '{{%fk-track_artist-artist_id}}',
            '{{%track_artist}}'
        );
        $this->dropForeignKey(
            '{{%fk-track_artist-track_id}}',
            '{{%track_artist}}'
        );
        $this->dropIndex(
            '{{%idx-unique-track_artist-track_id-artist_id}}',
            '{{%track_artist}}'
        );
        $this->dropTable('{{%track_artist}}');
        $this->dropTable('{{%artist}}');
        $this->dropTable('{{%track}}');
    }
}
