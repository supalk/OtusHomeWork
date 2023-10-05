drop table if exists users;

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
