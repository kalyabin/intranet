# intranet
Базовая система интранет для бизнес-центра

## Установка backend

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
 
 6. Для prod-окружения настроить прокси nginx+php-fpm в соответствии с рекомендациями Symfony 3.3: https://symfony.com/doc/current/setup/web_server_configuration.html
 
 7. Для dev-окружения запустить php web-server:
 
 ```./bin/console server:run```

### Тестирование

```./vendor/bin/phpunit```

 
