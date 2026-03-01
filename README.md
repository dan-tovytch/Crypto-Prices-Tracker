# Crypto Prices Tracker

This project is a cryptocurrency prices tracker developed with laravel. It consumes the public CoinGecko API, stores the data in a PostgreSQL and updates automatically every 5 minutes using laravel's built-in scheduler

## Architecture

The application uses Laravel's native job and queue system 
to process each coin asynchronously. A scheduled command 
runs every 5 minutes, dispatching individual jobs to the 
queue for each tracked cryptocurrency. This ensures that 
failures in one coin do not affect the processing of others.

## Functionalities

- Fetches real-time cryptocurrency data (prices, volumes, market cap, etc)
- Stores historical price data in the database
- Provides an Artisan command for manual execution `php artisan app:crypto`
- Processes queued jobs (if using Redis) with `php artisan queue:work redis`
- Automatic periodic updates via laravel scheduler
- Real-time price updates via WebSocket (Laravel Reverb)

## Requirements

- PHP 8.4 or higher
- Composer 
- PostgreSQL 16+
- Redis (optional, for cache/queues)
- PHP extensions: PDO, pgsql, redis (if using Redis)
- Docker (optional, for containerized environment)

## Installation

1. Clone the repository

    ```bash
    git clone https://github.com/dan-tovytch/Crypto-Prices-Tracker.git
    cd crypto-price-tracker
    ```

2. Install PHP dependencies

    ```bash
    composer install
    ```

3. Set up environment file

    ```bash
    cp .env.example .env  // for docker
    cd src
    cp .env.example .env
    ```

4. Generate application key

    ```bash
    php artisan key:generate
    ```

5. Configuration database in `.env`

    ```
    DB_CONNECTION=pgsql
    DB_HOST=db
    DB_PORT=5432
    DB_DATABASE=your_database
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```

6. Run migrations to create tables

    ```bash
    php artisan migrate
    ````

7. (Optional) Configure Redis 
    ```
    QUEUE_CONNECTION=redis
    REDIS_HOST=redis
    REDIS_PORT=6379
    REDIS_PASSWORD=null
    ```

## Docker setup:

If you prefer using Docker, the project includes a ```docker-compose.yml``` file with services for PHP, PostgreSQL, and Redis.

```bash
docker-compose up --build -d
```

After containers are up, run migrations inside the app container:

```bash
docker-compose exec app php artisan migrate
```

## Running the Command Manually

To test the data fetch and insertion, execute:

```bash
php artisan app:crypto
```

If everything is configured correctly, you should see a success message and the data will be stored in the database.

If you're using Redis for queues, you may also need to run the queue worker to process jobs (if any):

```bash
php artisan queue:work redis
```

## ⏰ Automatic Scheduling

The scheduled task is defined in ```app/Console/Commands/CryptoCommand.php``

Since we use `php artisan schedule:work` in the container scheduler, Laravel takes care of executing the task at the correct times without the need for cron. This approach is ideal for Docker environments.

## 📄 License

This project is open-source and licensed under the MIT License.