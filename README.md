# Laravel Code Coverage Workshop

## Installation

1. Clone the repository:

    ```bash
    gh repo clone haythembenkhlifa/code-coverage-workshop
    cd code-coverage-workshop
    ```

2. Install composer:

    ```bash
    composer install
    ```

3. Set up the environment:

    ```bash
    cp .env.example .env
    ./vendor/bin/sail up -d
    ./vendor/bin/sail artisan key:generate
    ```
