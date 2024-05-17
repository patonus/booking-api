## Running the application

The project is dockerized with the use of [`Laravel Sail`](https://laravel.com/docs/11.x/sail). To run it, you need to call several commands:

1. Copy the .env.example file:

```
    cp .env.example .env
```

2. Install dependencies using a [helper container](https://laravel.com/docs/11.x/sail#installing-composer-dependencies-for-existing-projects):

```
    docker run --rm \
        -u "$(id -u):$(id -g)" \
        -v "$(pwd):/var/www/html" \
        -w /var/www/html \
        laravelsail/php83-composer:latest \
        composer install --ignore-platform-reqs
```

3. Generate the encryption key:

```
    ./vendor/bin/sail artisan key:generate
```

4. Run the migrations with optional seed:

```
    ./vendor/bin/sail artisan migrate --seed
```
