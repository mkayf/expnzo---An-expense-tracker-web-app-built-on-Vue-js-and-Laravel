#!/bin/bash
set -e

PORT="${PORT:-10000}"
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/" /etc/apache2/sites-available/000-default.conf

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force || echo "WARNING: migration failed, check database connection"

# queue worker
(while true; do
    runuser -u www-data -- php artisan queue:work --sleep=3 --tries=3 --max-time=3600
    sleep 2
done) &


# schedule worker
(while true; do
    runuser -u www-data -- php artisan schedule:work
    sleep 2
done) &

exec apache2-foreground