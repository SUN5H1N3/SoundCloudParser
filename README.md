<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Sound Cloud Parser</h1>
    <br>
</p>

Описание проекта
-------------------

Необходима версия PHP >= 7.4.0.

Конфиг подключения к БД находится в файле `config/db.php`

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=soundcloudparser',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
];
```

Для создания всех таблиц необходимо запустить миграции (php yii migrate)

Посмотреть как работает парсер можно используя командную строку.

Для этого были реализованы следующие команды:

      php yii parse/artist [artist-slug] [?parser]             Запарсить исполнителя
      php yii parse/tracks [artist-slug] [?parser] [?limit]    Запарсить треки исполнителя
      php yii parse/link-tracks-artists [?artist-slug]         Связать треки с их исполнителями
      php yii parse/help                                       Показать эти команды

Примеры команд:

      php yii parse/artist lakeyinspired              Запарсить исполнителя https://soundcloud.com/lakeyinspired
      php yii parse/artist lakeyinspired api          Запарсить используя api SoundCloud
      php yii parse/artist lakeyinspired html         Запарсить используя html страницу исполнителя
      php yii parse/artist lakeyinspired stable       Запарсить используя stable парсер 
                                                      (парсер включающий наиболее паботоспособные методы, работает по умолчанию, можно не указывать)

      php yii parse/tracks lakeyinspired              Запарсить треки исполнителя. По дефолту лимит в 10 трэков
      php yii parse/tracks lakeyinspired api          Запарсить используя api SoundCloud
      php yii parse/tracks lakeyinspired html         Запарсить используя html страницу исполнителя
      php yii parse/tracks lakeyinspired stable       Запарсить используя stable парсер 
      php yii parse/tracks lakeyinspired stable 20    Запарсить 20 трэков (по доступности)
      


Описание файлов
------------

### SoundCloudParser.php

Главный абстрактный класс для парсинга SoundCloud.

Находится по пути `components/soundcloud/parsers/SoundCloudParser.php`.

В нем нет конкретных реализаций парсинга, а присутствуют абстрактные методы:

```php
abstract public function parseArtist(string $slug): Artist;
abstract public function parseTracks(string $artistSlug, int $limit = NULL): array;
```

От него унаследовано два класса `SoundCloudParserApi.php` и `SoundCloudParserHtml.php`, которые реализуют данные методы.

Также был унаследован класс `StableSoundCloudParser.php`, содержащий в себе стабильные (дающие более широкий объем данных) методы.
То есть он просто ссылается на другие парсеры, реализуя свои методы.
Можете опираться на этот файл, чтобы увидеть наиболее удачные для парсинга способы.

### SoundCloudParseCreator.php

Путь: `components/soundcloud/SoundCloudParseCreator.php`

Класс для "создания" абстрактного парсера, для того чтобы не привязываться в коде к конкретным.

### ParseController.php - Замена требуемого файла задания Example.php

Путь: `commands/ParseController.php`

Методы демонстрирующие работу с классом:

```php
public function actionTracks(string $slug, string $parserId = NULL, int $limit = 10): void;
public function actionArtist(string $slug, string $parserId = NULL): void
```

Именно здесь и реализуются консольные команды

### m211024_011157_create_soundcloud_tables.php - Замена требуемого файла задания Db.sql

Путь: `migrations/m211024_011157_create_soundcloud_tables.php`

Файл содержит в себе миграции для создания требуемых таблиц.

Он не содержит в себе SQL запросов в чистом виде. 

Если это принципиально я могу сделать именно SQL файл, но, работая с данным фреймворком, в конкретном случае в нем нет смысла.

Примечания
------------

Не были добавлены описания методам, но главное описание проекта есть здесь. Так как это лишь тестовый проект, решил опустить эту часть.

В проекте остались некоторые не почищенные файлы, предоставляемые базовым шаблоном Yii (базовый шаблон, потому что для тестового он был удобнее, но скорее всего лучше использовать advanced, так как его будет легче расширить).

Главные директории, в которых велась основная работа:

        commands/
        components/
        migrations/
        models/
