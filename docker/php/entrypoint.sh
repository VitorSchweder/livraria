#!/usr/bin/env bash
set -euo pipefail

if [ "${1:-}" = "php-fpm" ]; then
    if [ ! -f vendor/autoload_runtime.php ]; then
        composer install --no-interaction --prefer-dist
    fi

    until pg_isready -h "${DB_HOST:-db}" -p "${DB_PORT:-5432}" -U "${POSTGRES_USER:-livraria}" >/dev/null 2>&1; do
        echo "Aguardando o PostgreSQL..."
        sleep 1
    done

    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration --env=test
fi

exec docker-php-entrypoint "$@"
