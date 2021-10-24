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
                'soundcloud_id' => $this->integer()->unique(),
                'slug' => $this->string()->unique()->notNull(),
                'username' => $this->string(),
                'full_name' => $this->string(),
                'first_name' => $this->string(),
                'last_name' => $this->string(),
                'city' => $this->string(),
                'country_code' => $this->string(),
                'verified' => $this->boolean(),
                'likes_count' => $this->integer(),
                'followers_count' => $this->integer(),
                'followings_count' => $this->integer(),
                'tracks_count' => $this->integer(),
            ],
        );
        $this->createTable(
            '{{%track}}',
            [
                'id' => $this->primaryKey(),
                'soundcloud_id' => $this->integer()->unique(),
                'artist_id' => $this->integer(),
                'name' => $this->string(),
                'performer' => $this->string(),
                'slug' => $this->string(),
                'artist_slug' => $this->string(),
                'genre' => $this->string(),
                'duration' => $this->integer()->comment('ms'),
                'publication_date' => $this->dateTime(),
                'playback_count' => $this->integer(),
                'comment_count' => $this->integer(),
            ]
        );
        $this->addForeignKey(
            '{{%fk-track-artist_id}}',
            '{{%track}}',
            '[[artist_id]]',
            '{{%artist}}',
            '[[id]]'
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
            '{{%fk-track-artist_id}}',
            '{{%track}}'
        );
        $this->dropTable('{{%artist}}');
        $this->dropTable('{{%track}}');
    }
}
