#### Установка

`git clone git@github.com:TimurTurdyev/lk-client-migration-api.git`

#### Установить пакеты

`composer install --no-dev`

#### Скопировать
`cp .env.example .env`

`touch database/database.sqlite`
#### в .env
> DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite
#### для ЛК секция
> *DB_...._LK=*

Вход в админ панель 

`php artisan orchid:admin`
и создать пользователя
