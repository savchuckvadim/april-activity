Educational project - on skillfactory course.
API Backend
for start dev:
1) copy from .env.example to .env
2) create BD to localhost Apache and change env :

`DB_CONNECTION=mysql

DB_HOST=127.0.0.1`

`DB_PORT=3306`

`DB_DATABASE=modul_38`

`DB_USERNAME=root`

`DB_PASSWORD=`

3) Install dependences: `composer install`

4) `php artisan migrate:fresh --seed`
5) `php artisan serve` 
6) project starting on http://127.0.0.1:8000