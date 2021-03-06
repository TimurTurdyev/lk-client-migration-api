| Dependency |
|------------|
| php8       |
| sqlite3    |
| mysql      |
| php-dom    |

> На случай если будет импорт большого файла

| Service | Variable             | value |
|---------|----------------------|-------|
| PHP     | upload_max_filesize  | 128M  |
| PHP     | post_max_size        | 128M  |
| NGINX   | client_max_body_size | 128M  |

### Установка

`git clone git@github.com:TimurTurdyev/lk-client-migration-api.git`

#### Установить пакеты
| Server     | Params                       |
|------------|------------------------------|
| Боевой     | `composer install --no-dev`  |
| Локальный  | `composer install`           |

При возникновении трудностей с локальным окружением добавить флаг `--ignore-platform-reqs`
Либо запустить composer от `php8.0 -f /usr/local/bin/composer [flags]`

#### Скопировать
`cp .env.example .env`

`touch database/database.sqlite`

#### Прописать .env

> Для локальной <br> DB_CONNECTION=sqlite <br>
DB_DATABASE=/absolute/path/to/database.sqlite <br><hr> 
> Для ЛК секция <br> DB_................_LK=*


И выполнить `php artisan migrate`

#### Вход в админ панель 

> Создать пользователя `php artisan orchid:admin`

### Обновление

`composer update` or `php8.0 -f /path-to/composer update` <br>
И выполнить `php artisan migrate`
