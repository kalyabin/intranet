#!/bin/bash
# wait-for-postgres.sh

until PGPASSWORD=$POSTGRES_DB_PASSWORD psql $POSTGRES_DB_NAME -h "$POSTGRES_DB_HOST" -U "$POSTGRES_DB_USER" -c '\q'; do
  >&2 echo "Development Postgres is unavailable - sleeping"
  sleep 1
done

until PGPASSWORD=$POSTGRES_DB_TEST_PASSWORD psql $POSTGRES_DB_TEST_NAME -h "$POSTGRES_DB_TEST_HOST" -U "$POSTGRES_DB_TEST_USER" -c '\q'; do
  >&2 echo "Testing Postgres is unavailable - sleeping"
  sleep 1
done

>&2 echo "Postgres is up - executing command"
exec $cmd
