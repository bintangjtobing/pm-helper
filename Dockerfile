# syntax=docker/dockerfile:1

# Stage 1: Build frontend assets
FROM node:18-bullseye-slim AS frontend

WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm install
COPY resources/ resources/
COPY vite.config.js postcss.config.js tailwind.config.js ./
RUN npm run build

# Stage 2: Application
FROM node:18-bullseye-slim

WORKDIR /app

COPY .env.example .env
COPY . .

# Copy built frontend assets from stage 1
COPY --from=frontend /app/public/build public/build
COPY --from=frontend /app/node_modules node_modules

RUN apt-get update -y && \
    apt-get install -y --no-install-recommends software-properties-common gnupg2 wget lsb-release && \
    echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/sury-php.list && \
    wget -qO - https://packages.sury.org/php/apt.gpg | apt-key add - && \
    apt-get update -y && \
    apt-get install -y --no-install-recommends php8.1 php8.1-curl php8.1-xml php8.1-zip php8.1-gd php8.1-mbstring php8.1-mysql && \
    apt-get install -y --no-install-recommends composer && \
    composer install --no-dev --optimize-autoloader && \
    php artisan key:generate && \
    rm -rf /var/lib/apt/lists/*

CMD [ "bash", "./run.sh"]
