# Laravel Code Coverage Workshop

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
