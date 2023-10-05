# Обучение (24.08.2023 - ... )
### (OTUS Highload Architect) ### 
Разработка социальной сети.  

Postman-коллекция  [wh_sak.postman_collection.json](report/wh_sak.postman_collection.json)

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

  `docker-compose exec php php cli.php app:createTable`
 
- Запускаем генерацию анкет (N - 1 000 000).  
Можно дополнительным параметром передать количество генерируемых анкет 

  - `docker-compose exec php php cli.php app:fillingUsers`
  - `docker-compose exec php php cli.php app:fillingUsers 100`
  
Для авторизации можно использовать пару Логин: user_{N} и Пароль: psw{N}

- Готово! http://localhost:8080/
 
  `docker compose ps`
 
## Домашние задание
1. [Заготовка для социальной сети](./report/work-1/readme.md)
2. [Производительность индексов](./report/work-2/readme.md)


