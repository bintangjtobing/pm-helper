#!/bin/bash
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link 2>/dev/null || true
npm run build
php artisan optimize:clear
php artisan queue:work --tries=3 &
php artisan serve --host 0.0.0.0
