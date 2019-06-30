### How to setup this repository
```
$ cd [YOUR PROJECT FOLDER]
$ git clone https://github.com/kondows95/laravel-rest-api-sample.git laravel
$ cd laravel

$ cp .env.example .env
Edit ".env" as following.
DB_HOST=127.0.0.1
DB_DATABASE=sample_db
DB_USERNAME=root
DB_PASSWORD=

$ touch .env.testing
Edit ".env.testing" as following.
DB_HOST=127.0.0.1
DB_DATABASE=sample_db_testing
DB_USERNAME=root
DB_PASSWORD=

Opne your MySQL (version 5.7 needed)
$ mysql -u root
mysql> CREATE SCHEMA `sample_db` DEFAULT CHARACTER SET utf8;
mysql> CREATE SCHEMA `sample_db_testing` DEFAULT CHARACTER SET utf8;
mysql> quit;

$ composer install
$ php artisan key:generate

$ php artisan migrate --seed
$ php artisan migrate --seed --env=testing

$ ./vendor/bin/phpunit
```
