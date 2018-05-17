# intranet
Базовая система интранет для бизнес-центра

## Требования

* PHP 7.1+
* Composer 1.4+
* PostgreSQL 9.4+
* NodeJS 7.9+
* npm 4.0+
* TypeScript 2.3+

## Docker

```docker-compose build && docker-compose up```

После успешного запуска backend-контейнера в браузере открыть:

http://localhost:3000/

TODO: выполнить предустановку в контейнере фикстур и описать процесс авторизации в интранете

http://

## Окружение

### Установка backend

1. Перейти в папку backend
2. Выполнить команду ```composer install```
3. При установке проекта указать:
  * database_host - хост для подключения к PostgreSQL базе данных
  * database_port - порт для подключения к PostgreSQL базе данных
  * database_name - название PostgreSQL базы данных
  * database_user - пользователь для PostgreSQL базы данных
  * database_password - пароль для PostgreSQL пользователя
  * database_test_host - хост для подключения к тестовой базе данных
  * database_test_port - порт для подключения к тестовой базе данных
  * database_test_name - название для тестовой базы данных
  * database_test_user - пользователь для тестовой базы данных
  * database_test_password - пароль для тестового пользователя
  * mailer_transport - транспорт для почты SwiftMailer: gmail, smtp, mail
  * mailer_host - SMTP хост для транспорта smtp или gmail
  * mailer_user - SMTP пользователь для транспорта smtp или gmail
  * mailer_password - пароль для SMTP пользователя
  * secret - секретный ключ для шифрации сессий
  * default_email_from - ящик отправителя по умолчанию
  * comet_url - URL для фронтенда для WebSocket - подключения (порт для фронтенд-приложения по умолчанию - 3001, настраивается в приложении frontend)
  * ats_password - пароль для RestFul-сервиса ATS Asterisk
 4. Создать в PostgreSQL базы данные, указанные на шаге 3.
 5. Выполнить миграции для БД:
 
 Для рабочего окружения:
 ```./bin/console doctrine:migrations:migrate --env=[prod|dev]``` - в зависимости от окружения выбрать prod или dev
 
 Для тестового окружения:
 ```./bin/console doctrine:migrations:migrate --env=test```
 
 6. Для prod-окружения настроить прокси nginx+php-fpm применить типовой конфиг из файла nginx.sample.conf.
 7. Для dev-окружения запустить php web-server:
 
 ```./bin/console server:run```

### Тестирование backend

```./vendor/bin/phpunit```

### Установка frontend

1. Перейти в папку frontend
2. Выполнить команду ```npm install```
3. Для prod-окружения выполнить сборку проекта командой ```npm run build```

### Тестирование frontend

TODO: тестов пока нет

### Comet-сервер

Конфигурация comet-сервера расположена в файле `frontend/comet.config.js`. По умолчанию comet-сервер смотрит на порт 0.0.0.0:3001, возможно настроить проксирование иного порта через nginx.

### Запуск проекта

#### Backend

1. Для dev-окружения запустить php web-server командой: ```./backend/bin/console server:run```
2. Для prod-окружения настройки php+nginx указаны выше

#### Frontend

1. Для dev-окружения запустить webpack-сервер командой ```npm start``` (порт веб-сервера по умолчанию - 3000)
2. Для prod-окружения настройки php+nginx указаны выше

Для всех окружений требуется запуск comet сервера командой ```npm run comet``` в папке frontend (можно автоматизировать, с помощью контроллера процессов, например через supervisor).

Для dev-окружения проект открывается по адресу http://localhost:3000/ в браузере.
Для prod-окружения проект открывается по адресу http://yourhost.ltd/ в браузере.

## Работа с консольными командами

Основные команды (сброс кеша, миграции БД, запуск веб-сервера и т.д. указаны в документации Symfony). В данном файле описаны кастомные команды для проекта.

### Создание пользователей

```./backend/bin/console user:create-user```

### Создание категорий для тикетной системы

```./backend/bin/console ticket:category:create```

### Закрытие устаревших тикетов

```./backend/bin/console ticket:close-expired-tickets```

### Создание арендатора

```./backend/bin/console customer:create-customer```


 ## TODO
 
1. Описать основные бизнес-процессы, реализуемые через данный проект для бизнес-центров.
2. Реализовать unit-тестирование frontend-приложения
3. Продолжить работу над проектом :)
