--drop table if exists users;

CREATE TABLE IF NOT EXISTS users (
    user_id bigserial primary key not null,
    login varchar not null UNIQUE,
    password varchar not null,
    name varchar(255) not null,
    surname varchar(255) null,
    lastname varchar(255) null,
    gender smallint null,
    biography varchar null,
    city varchar null,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS log_db_query (
    id serial primary key NOT NULL,
    user_id int NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    query_sql text NOT NULL,
    query_time int NULL,
    query_errors text NULL
);
