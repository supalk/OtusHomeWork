CREATE TABLE IF NOT EXISTS log_db_query (
    id serial primary key NOT NULL,
    user_id int NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    query_sql text NOT NULL,
    query_time int NULL,
    query_errors text NULL
);
