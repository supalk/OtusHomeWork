
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

CREATE TABLE IF NOT EXISTS friends (
    user_id bigint not null,
    friend_id bigint not null,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_id
      FOREIGN KEY(user_id)
	  REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_friend_id
      FOREIGN KEY(friend_id)
	  REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS posts (
    id uuid PRIMARY KEY,
    text text not null,
    author_user_id bigint not null,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_user_id
      FOREIGN KEY(author_user_id)
	  REFERENCES users(user_id) ON DELETE CASCADE
);
