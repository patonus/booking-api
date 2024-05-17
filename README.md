# Booking API

## Testing

This application has seeding implemented for easier testing. The `Vacancy` seed generates 10 random vacancies + 3 for first available days.
To simplify the testing process, there is a [Postman collection](https://www.postman.com/patonus/workspace/booking-api/collection/6244938-30543787-9135-4009-96c6-77e22e6d40b1?action=share&creator=6244938) created.

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

3. Run the containers:

```
    ./vendor/bin/sail up
```

4. Generate the encryption key:

```
    ./vendor/bin/sail artisan key:generate
```

5. Run the migrations with optional seed:

```
    ./vendor/bin/sail artisan migrate --seed
```

## Assumptions

There are some assumptions I made that shaped the solution I provided:

-   The vacancies are interchangeble on a given date. That's not how it would work in real life, however that's what I understand you meant in the task.
-   Large scale performance isn't the focus of this task, so I didn't create any solutions for, apart of thing that are a good practice in general. I am a firm believer that "premature optimization is the root of all evil".
-   Docker is only for the ease of checking solution. `Laravel Sail` is not suitable for production.
-   In a real app, there would probably be a need for a m2m relationship between `Reservation` and `Vacancy` to keep track of which reservation occupies which vacancy. But it doesn't seem to be needed for completion of this task, so I decided to omit it for simplicity.
-   The code is quite heavily commented. I would remove most of them in a production app, however it might be useful to help follow my logic.

## Improvements

There are lots of ways in which I would extend the solution if I had unlimited time:

-   Implement performance and error tracking, I'm a fan of [Sentry](https://sentry.io), so I would probably choose it.
-   Documentation, especially generate an OpenAPI spec. I would use a tool that automatically generate it from code, something like [`L5-Swagger`](https://github.com/DarkaOnLine/L5-Swagger), so it wouldn't become outdated. This would then be used to generate types on the front-end, or even whole `@tanstack/query` hooks using something like [`Orval`](https://orval.dev/). This way we would get close to end-to-end type safety and have lots of generated code.
-   Support multiple languages - useful for error messages. This could be achieved two ways. Either translate them on the backend, or return an error-type enum to the front-end, and make the front-ends responsibility to interpret it.
-   Logging, especially an audit log. Assuming that the data load wouldn't be huge, something like [`laravel-auditing`](https://laravel-auditing.com/) is invaluable for debugging when something goes wrong.
-   Cache for responses. A short-lived one on the front-end (for example a couple of seconds) and a longer-lived one on the backend. I would introduce it only as needed.
