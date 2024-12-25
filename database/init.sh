#!/bin/bash
DB_HOST="localhost"
DB_NAME="vhost15998s0"
DB_USER="vhost15998s0"
DB_PASS="Digiplayer1-401"

echo "Initializing database..."

# Run migrations
for migration in migrations/*.sql; do
    echo "Running migration: $migration"
    mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < $migration
done

# Run seeds
for seed in seeds/*.sql; do
    echo "Running seed: $seed"
    mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < $seed
done

echo "Database initialization completed!"
