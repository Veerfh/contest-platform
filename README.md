## Начало работы

```bash
# 1. Клонировать репозиторий
git clone <repository-url>
cd contest-platform

# 2. Установить зависимости PHP
composer install

# 2. ИЛИ
composer update

# 4. Скопировать .env файл
cp .env.example .env
```

Далее необходимо установить или запустить приложение Docker

```bash
# 6. Запустить Docker контейнеры (MinIO)
docker-compose up -d

# 7. Создать bucket в MinIO (файл уже есть в корне проекта)
php create-bucket.php

```

Console MinIO будет находиться по этому адресу http://localhost:9001/browser/contests

Данные для входа в MinIO(если понадобится):
логин: minioadmin
пароль: minioadmin

```bash

# 9. Запустить миграции и сиды
php artisan migrate --seed

# 10. Запустить очередь
php artisan queue:work

# 11. Запустить сервер(в отдельном терминале)
php artisan serve
```

ТЕСТОВЫЕ ДАННЫЕ ДЛЯ ВХОДА:

Пользователь
логин: participant@example.com
пароль: password

Жюри
логин: jury@example.com
пароль: password

Админ
логин: admin@example.com
пароль: password
