# Производительность индексов
### Разработанные методы:
- `user/search`  с авторизацией Cookie

## Тестирование производительности с индексом и без

* генерация тестовых данных командой `docker-compose exec php php cli.php app:fillingUsers`
* [сценарий(jmeter) для создания нагрузки](file/HighloadTesting.jmx).   
Необходимы установленные дополнительные плагины для Jmeter `graphs-additional-2.0`
* Создание индексов
  `docker-compose exec php php cli.php app:createIndex`
* Удаление индексов
`docker-compose exec php php cli.php app:deleteIndex`

## Методология тестирования
* при тестировании приложения, бд и создаваемая нагрузка находятся на разных машинах
* нагрузка создается при помощи приложения jmeter.
    * Приложение отправляет запросы на поиск по имени и фамилии
* тестирование проводится в 4 этапа
    * в 1 поток
    * в 10 потоков
    * в 100 потоков
    * в 1000 потоков
* после создания индекса для оптимизации поиска тесты повторяются
* после каждого замера приложение и бд перезапускаются

Sql-запрос
````
select user_id, name, surname, lastname, gender, biography, city 
from users
where
lower(name) like 'ольг%'
and lower(surname) like 'каз%'
````
 
##  Без индекса
Exlplain![img_3.png](file/img_3.png)

### 1. Этап.  
   - Поток: 1
   - Количество запросов: 100
 
#### _Latency_
![Latency-1](file/img_5.png)
### _Throughput_
![Throughput-1](file/img_2.png)
### _Summary_
![Summary-1](file/img_4.png)
___
### 2. Этап.  
   - Поток: 10
   - Количество запросов: 100
 
#### _Latency_
![Latency-2](file/img_6.png)
### _Throughput_
![Throughput-2](file/img_7.png)
### _Summary_
![Summary-2](file/img_8.png)
___
### 3. Этап.  
   - Поток: 100
   - Количество запросов: 100
 
#### _Latency_
![Latency-3](file/img_9.png)
### _Throughput_
![Throughput-3](file/img_10.png)
### _Summary_
![Summary-3](file/img_11.png)
___
### 4. Этап.  
   - Поток: 1000
   - Количество запросов: 1

Загрузка в момент нагрузки.
![img_12.png](file/img_12.png)
#### _Latency_
![Latency-4](file/img_13.png)
### _Throughput_
![Throughput-4](file/img_14.png)
### _Summary_
![Summary-4](file/img_15.png)

___

## Создаём индекс для оптимизации запроса поиска
>CREATE INDEX idx_users_name_surname ON users USING btree (lower("name") varchar_pattern_ops,lower(surname) varchar_pattern_ops);
##  С индексом
Exlplain![img_17.png](file/img_17.png)
### 1. Этап.
- Поток: 1
- Количество запросов: 100

#### _Latency_
![Latency-1](file/img_18.png)
### _Throughput_
![Throughput-1](file/img_19.png)
### _Summary_
![Summary-1](file/img_20.png)
___
### 2. Этап.
- Поток: 10
- Количество запросов: 100

#### _Latency_
![Latency-2](file/img_21.png)
### _Throughput_
![Throughput-2](file/img_22.png)
### _Summary_
![Summary-2](file/img_23.png)
___
### 3. Этап.
- Поток: 100
- Количество запросов: 100

#### _Latency_
![Latency-3](file/img_24.png)
### _Throughput_
![Throughput-3](file/img_25.png)
### _Summary_
![Summary-3](file/img_26.png)
___
### 4. Этап.
- Поток: 1000
- Количество запросов: 1

#### _Latency_
![Latency-4](file/img_27.png)
### _Throughput_
![Throughput-4](file/img_28.png)
### _Summary_
![Summary-4](file/img_29.png)
