## Notification Service

Микросервис для массовой рассылки SMS и Email уведомлений с поддержкой приоритетов, идемпотентности и гарантированной доставкой.

## Технологический стек

- **PHP 8.3** + **Laravel 13**
- **PostgreSQL 17** — хранение уведомлений
- **Redis 7** — дедубликация и exactly-once
- **RabbitMQ 3.13** — брокер сообщений с приоритетами
- **Nginx** — веб-сервер
- **Docker** + **Docker Compose** — контейнеризация

## Требования

- Docker (>= 20.10)
- Docker Compose (>= 2.0)

## Быстрый старт

### 1. Клонировать репозиторий

git clone https://github.com/your-username/notification-service.git
cd notification-service

### 2. Настроить окружение

cp docker_notifs/.env.example docker_notifs/.env

### 3. Запустить контейнеры

cd docker_notifs
docker-compose up -d

### 4. Установить зависимости Laravel

docker exec -it notifs-php-fpm sh
composer install
php artisan migrate
php artisan key:generate
exit

### 5. Запустить воркера

docker exec -it notifs-php-fpm php artisan rabbitmq:work notifications

## API Документация

После запуска сервиса документация доступна по адресу:
http://localhost:8083/docs/api

## Статусы уведомлений

| Статус     | Значение                                      |
|------------|-----------------------------------------------|
| queued     | Сообщение принято и ожидает отправки          |
| sent       | Передано провайдеру/шлюзу                     |
| delivered  | Подтверждено провайдером                      |
| failed     | Ошибка доставки                               |

## Архитектура

API Request → Controller → Redis (дедубликация) → БД (статус queued)
↓
RabbitMQ (приоритетная очередь)
↓
Consumer Worker (php artisan rabbitmq:work)
↓
SendNotificationJob (retry 3 раза)
↓
Mock Provider (SMS/Email)
↓
БД (обновление статуса)

## Ключевые возможности

### Приоритезация трафика
- Критические уведомления (is_critical: true) получают приоритет 10
- Обычные рассылки имеют приоритет 0
- RabbitMQ отдаёт сообщения с высоким приоритетом первыми

### Гарантия доставки (at-least-once)
- Ручное подтверждение (ack) в consumer'е
- При ошибке сообщение возвращается в очередь
- Job имеет 3 попытки с задержками 5, 15, 30 секунд

### Идемпотентность
- Дедубликация через Redis (ключ на 24 часа)
- Exactly-once через Redis ключ processing:{id}

### Персистентность
- Очереди RabbitMQ durable
- Сообщения с delivery_mode = 2 (сохраняются на диск)
- PostgreSQL для хранения статусов

## Запуск тестов

docker exec -it notifs-php-fpm php artisan test

Или конкретный тест:

docker exec -it notifs-php-fpm php artisan test --filter BroadcastTest

## Переменные окружения

Создайте файл .env в директории docker_notifs/:

CONTAINER_PREFIX=notifs

# PostgreSQL
POSTGRES_DB=notifs
POSTGRES_USER=notifs
POSTGRES_PASS=notifs_pass

# RabbitMQ
RABBIT_USER=notifs
RABBIT_PASS=notifs_pass

# Структура проекта

```text
docker_notifs/
├── docker-compose.yml
├── nginx/
│   └── default.conf
├── php-fpm/
│   └── Dockerfile
└── .env

core/
├── app/
├── config/
├── database/
├── routes/
└── tests/
```