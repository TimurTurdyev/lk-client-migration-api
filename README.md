| Dependency |
|------------|
| php8       |
| sqlite3    |
| mysql      |

### Установка

`git clone git@github.com:TimurTurdyev/lk-client-migration-api.git`

#### Установить пакеты

> Боевой `composer install --no-dev` <br>
Локальный `composer install`

При возникновении трудностей с локальным окружением добавить флаг `--ignore-platform-reqs`

#### Скопировать
`cp .env.example .env`

`touch database/database.sqlite`

#### Прописать .env

> Для локальной <br> DB_CONNECTION=sqlite <br>
DB_DATABASE=/absolute/path/to/database.sqlite <br><hr> Для ЛК секция <br> *DB_...._LK=*


И выполнить `php artisan migrate`

#### Вход в админ панель 

> Создать пользователя `php artisan orchid:admin`

### Обновление

`composer update` <br>
И выполнить `php artisan migrate`
