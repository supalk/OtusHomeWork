#!/bin/bash
set -e

cat /tmp/postgresql.conf >> /var/lib/postgresql/data/postgresql.conf
cat /tmp/pg_hba.conf >> /var/lib/postgresql/data/pg_hba.conf

# Replication
psql -v ON_ERROR_STOP=1 --username "$DB_USERNAME" --dbname "$DB_DATABASE" <<-EOSQL
	CREATE ROLE replication_user WITH LOGIN PASSWORD 'password' REPLICATION;
	GRANT CONNECT ON DATABASE $DB_DATABASE TO replication_user;
EOSQL

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$DB_DATABASE" <<-EOSQL
	GRANT SELECT ON ALL TABLES IN SCHEMA public TO replication_user;
	GRANT SELECT ON ALL SEQUENCES IN SCHEMA public TO replication_user;
	GRANT USAGE ON SCHEMA public TO replication_user;
	ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO replication_user;
EOSQL
