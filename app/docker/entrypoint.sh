#!/usr/bin/env sh
set -eu

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

exec "$@"
