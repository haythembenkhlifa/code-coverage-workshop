# Laravel Code Coverage Workshop

This is a simple Laravel project designed for a workshop to demonstrate best practices in API development, testing, and code coverage.

## Installation

1. Clone the repository:

    ```bash
    git clone <repository-url>
    cd code-coverage
    ```

2. Set up the environment:

    ```bash
    cp .env.example .env
    ./vendor/bin/sail up -d
    sail artisan key:generate
    sail artisan migrate
    sail artisan db:seed
    ```

3. Access the application at [http://localhost](http://localhost).

## Database Setup

This project uses SQLite for simplicity.

1. Ensure the following lines are in your `.env` file:
    ```env
    DB_CONNECTION=sqlite
    DB_DATABASE=/var/www/html/database/database.sqlite
    ```
2. Create the database file if it does not exist:
    ```bash
    touch database/database.sqlite
    ```
3. Run migrations and seeders:
    ```bash
    sail artisan migrate
    sail artisan db:seed
    ```
