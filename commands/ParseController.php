<?php

namespace app\commands;

use app\components\soundcloud\parsers\SoundCloudParser;
use app\components\soundcloud\SoundCloudParseCreator;
use app\models\Artist;
use app\models\Track;
use yii\base\Exception;
use yii\console\Controller;
use yii\db\ActiveRecord;

class ParseController extends Controller
{
    /**
     * @param string $slug
     * @param string|null $parserId
     * @param int $limit
     * @throws Exception
     */
    public function actionTracks(string $slug, string $parserId = NULL, int $limit = 10): void
    {
        $parser = $this->createParser($parserId);
        $tracks = $parser->parseTracks($slug, $limit);

        if (!$parser->hasErrors()) {
            foreach ($tracks as $track) {
                echo $this->saveModelWithMessage($track, $modelsSavedCount);
            }
        }
    }

    /**
     * @param string $slug
     * @param string|null $parserId
     * @throws Exception
     */
    public function actionArtist(string $slug, string $parserId = NULL): void
    {
        $parser = $this->createParser($parserId);
        $artist = $parser->parseArtist($slug);

        if (!$parser->hasErrors()) {
            echo $this->saveModelWithMessage($artist);
        }
    }

    /**
     * @param string|null $artistSlug
     */
    public function actionLinkTracksArtists(string $artistSlug = NULL): void
    {
        $tracksQuery = Track::find()
            ->where(['artist_id' => NULL])
            ->andFilterWhere(['artist_slug' => $artistSlug])
            ->orderBy('artist_slug');

        $counter = 0;

        /** @var Track[] $batch */
        foreach ($tracksQuery->batch() as $batch) {
            $artistSlugs = array_map(static fn(Track $track) => $track->artist_slug, $batch);
            $artistSlugs = array_unique($artistSlugs);

            $artists = Artist::find()
                ->select(['slug', 'id'])
                ->where(['slug' => $artistSlugs])
                ->indexBy('slug')
                ->all();

            foreach ($batch as $track) {
                if (array_key_exists($track->artist_slug, $artists)) {
                    $track->artist_id = $artists[$track->artist_slug]->id;

                    $prevCounter = $counter;
                    $message = $this->saveModelWithMessage($track, $counter);
                    if ($counter === $prevCounter) {
                        echo $message;
                    }
                }
            }
        }
        echo $counter . ' models saved.';
    }

    public function actionHelp(): void
    {
        $commandTemplates = [
            'php yii parse/artist [artist-slug] [?parser]',
            'php yii parse/tracks [artist-slug] [?parser] [?limit]',
            'php yii parse/link-tracks-artists [?artist-slug]',
            'php yii parse/help',
        ];

        echo 'Command templates:' . PHP_EOL;
        foreach ($commandTemplates as $template) {
            echo "\t" . $template . PHP_EOL;
        }
    }

    /**
     * @param string|null $parserId
     * @param array $config
     * @return SoundCloudParser
     */
    private function createParser(string $parserId = NULL, array $config = []): SoundCloudParser
    {
        $config['enableLiveLogs'] ??= true;
        return SoundCloudParseCreator::create($parserId, $config);
    }

    /**
     * @param ActiveRecord $model
     * @param int|null $counter
     * @return string
     */
    private function saveModelWithMessage(ActiveRecord $model, int &$counter = NULL): string
    {
        if (!$model->save()) {
            return 'Validation failed: ' . PHP_EOL . print_r($model->errors, true) . PHP_EOL;
        }

        $counter++;
        return "Saved model #{$counter}: " . PHP_EOL . print_r($model->getAttributes(), true) . PHP_EOL;
    }
}