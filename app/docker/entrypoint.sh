#!/usr/bin/env sh
set -eu

if [ "${APP_ENV:-}" = "production" ]; then
  if [ -z "${APP_KEY:-}" ] || [ "${APP_KEY:-}" = "base64:GERAR_NA_VPS_COM_PHP_ARTISAN_KEY_GENERATE" ]; then
    echo "APP_KEY must be generated before running in production." >&2
    exit 1
  fi

  if [ -z "${DB_PASSWORD:-}" ] || [ "${DB_PASSWORD:-}" = "troque_por_uma_senha_forte" ]; then
    echo "DB_PASSWORD must be changed before running in production." >&2
    exit 1
  fi
fi

if [ "${DB_CONNECTION:-}" = "pgsql" ]; then
  echo "Waiting for PostgreSQL at ${DB_HOST:-db}:${DB_PORT:-5432}..."
  php -r '
  $host = getenv("DB_HOST") ?: "db";
  $port = getenv("DB_PORT") ?: "5432";
  $db = getenv("DB_DATABASE") ?: "vmd";
  $user = getenv("DB_USERNAME") ?: "postgres";
  $pass = getenv("DB_PASSWORD") ?: "";
  $deadline = time() + 60;

  do {
      try {
          new PDO("pgsql:host={$host};port={$port};dbname={$db}", $user, $pass);
          exit(0);
      } catch (Throwable $e) {
          if (time() >= $deadline) {
              fwrite(STDERR, $e->getMessage() . PHP_EOL);
              exit(1);
          }
          sleep(2);
      }
  } while (true);
  '
fi

php artisan migrate --force

if [ "${APP_ENV:-}" = "production" ]; then
  php artisan config:cache
  php artisan view:cache
fi

exec "$@"
