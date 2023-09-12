# Домашнее задание "Заготовка для социальной сети" 
#### (OTUS Highload Architect) #### 
API скелет для социальной сети.  
Разработанные методы:
 - /login
 - /user/register
 - /user/get/{id}  с авторизацией Cookie
 
Postman-коллекция  wh_sak.postman_collection.json

Основано на Slim v4
 - PHP >= 8.1
 - Postgres 14.8
 - без ORM

### Требования (для локальной разработки)

Docker, docker compose

### Запуск локально
- Создаём конфиг
 
  `cp .env.example .env`

- Билдим контейнеры

  `docker compose build`

- Стартуем контейнеры

  `docker compose up` (с флагом `-d` если нужно запустить в режиме демона)

- Ставим зависимости

  `docker compose exec --user=www-data php composer install`

- Запускаем создание сущностей для БД 

  `http://localhost:8080/start`

- Готово! http://localhost:8080/

  `docker compose ps`
