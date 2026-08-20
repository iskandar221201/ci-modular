#!/bin/sh
set -e

# .env is generated at runtime from environment variables so credentials are
# never baked into the image. Dev defaults below; override in production
# (e.g. Dokploy env UI or docker-compose `environment:`).
if [ ! -f .env ]; then
    cp .env.example .env
fi

DB_HOSTNAME="${DB_HOSTNAME:-db}"
DB_DATABASE="${DB_DATABASE:-ci4pgk}"
DB_USERNAME="${DB_USERNAME:-root}"
DB_PASSWORD="${DB_PASSWORD:-root}"

sed -i "s|^database.default.hostname=.*|database.default.hostname=${DB_HOSTNAME}|" .env
sed -i "s|^database.default.database=.*|database.default.database=${DB_DATABASE}|" .env
sed -i "s|^database.default.username=.*|database.default.username=${DB_USERNAME}|" .env
sed -i "s|^database.default.password=.*|database.default.password=${DB_PASSWORD}|" .env

exec "$@"
