# Booking API

## Testing

This application has seeding implemented making testing easier. The `Vacancy` seed generates 10 random vacancies + 3 for first available days.
To simplify the testing process, there is a [Postman collection](https://www.postman.com/patonus/workspace/booking-api/collection/6244938-30543787-9135-4009-96c6-77e22e6d40b1?action=share&creator=6244938) created.

## API design

In most of my decisions regarding API design I tried to follow Laravel standards. That's why:

-   The naming convention of resource names in plural in URL (`/reservations` instead of `/reservation`).
-   The built in pagination method even though the front-end does not use all of the fields provided.
-   Error responses when input is incorrect have status 422.

For the `/resources` endpoint I chose to include all fields that are available from the model, as all the data may be useful in a production-grade API.

n general, I've tried to consider how the API could be extended, although it was often difficult to gauge how a toy project might theoretically be extended.

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

-   The vacancies are interchangeable on a given date. That's not how it would work in real life, however that's what I understand you meant in the task.
-   Large scale performance isn't the focus of this task, so I didn't create any solutions for, apart of thing that are a good practice in general. I am a firm believer that "premature optimization is the root of all evil".
-   Docker is only for the ease of checking solution. `Laravel Sail` is not suitable for production.
-   In a real app, there would probably be a need for a many-to-many relationship between `Reservation` and `Vacancy` to keep track of which reservation occupies which vacancy. But it doesn't seem to be needed for completion of this task, so I decided to omit it for simplicity.
-   The code is quite heavily commented. I would remove most of them in a production app, however it might be useful to help follow my logic.

## Improvements

There are lots of ways in which I would extend the solution if I had unlimited time:

-   Implement performance and error tracking, I'm a fan of [Sentry](https://sentry.io), so I would probably choose it.
-   Documentation, especially generate an OpenAPI spec. I would use a tool that automatically generate it from code, something like [`L5-Swagger`](https://github.com/DarkaOnLine/L5-Swagger), so it wouldn't become outdated. This could then be used to generate types on the front-end, or even whole `@tanstack/query` hooks using something like [`Orval`](https://orval.dev/). This way we would get close to end-to-end type safety and have lots of generated code.
-   Support multiple languages - useful for error messages. This could be achieved two ways. Either translate them on the backend, or return an error-type enum to the front-end, and make the front-ends responsibility to interpret it.
-   Logging, especially an audit log. Assuming that the data load wouldn't be huge, something like [`laravel-auditing`](https://laravel-auditing.com/) is invaluable for debugging when something goes wrong.
-   Cache for responses. A short-lived one on the front-end (for example a couple of seconds) and a longer-lived one on the backend. I would introduce it only as needed.
-   Linter. In my opinion every production project needs a linter. For Laravel projects I tend to choose [`PHPStan`](https://phpstan.org/).
-   CI. At the very least there should be a Github Action that runs tests and the linter on merges to main and on open pull requests.
-   API versioning. There are two main ways I would consider implementing it. Either inside of route (`/api/v{X}/...`) or as a custom header. Probably would go with the first one as it's more clear which version is used.

## Additional tasks

Due to lack of time I will only describe how I would approach implementing the additional tasks I didn't do.

### Priced dates and reservation price calculation

The endpoint would be `GET /reservations/price`. It would accept `start_date` and `end_date` in the same limitations and formats as `POST /reservations`, as query parameters.

#### Case #1: Only one currency is supported

I assume here that the currency has 2 decimal places.
The output would have one field:

-   `price` - represented with two decimal places representing the main denomination.

The database would incude one field:

-   `price` stored either as a `DECIMAL` with two decimal places representing the main denomination

The calculation would be implemented as a single db query `Vacancy::where('start_date', $startDate)->where('end_date', $endDate)->sum('price')`

#### Case #2: Multiple currencies are supported

The endpoint would accept the third param, `currency`, representing the currency in which price would be outputted
The output would have one field:

-   `price` - with two decimal places representing the main denomination or as an integer representing the smallest denomination.

The database schema would include two fields:

-   `price` stored either as a `DECIMAL` with two (or three, depending which currencies should be supported) decimal places representing the main denomination, or as an integer representing the smallest denomination (Would be the same as output format).
-   `currency` stored as the ISO code.

Additionally we would either need a service that provides price conversions, or, if the rates don't need to be very up to date, a table that keeps the conversion rates and a CRON job that updates them on an interval.

How the calculations would be implemented depends on the price conversion system.

### Additional endpoints

I would definately finish the CRUD of reservations, as it would be a must have in a production app:

-   Reading a reservation by ID - `GET /reservations/{ID}`, fairly straightforward.
-   Updating a reservation - `PUT /reservations/{ID}`. It would require in body `start_date` and `end_date`, as `PUT` is used to change the whole resource. Here, apart of the reservation itself, the vacancies would need to be updated as well.
-   Deleting a reservation - `DELETE /reservations/{ID}`. Apart of deleting the resource, the vacancies need to be updated as well.

Additionally, the `Vacancy` resource could use two endpoints:

-   Reading a list of them - `GET /vacancies`, fairly straightforward. It would be useful for improving the UX of the frontend app.
-   Creating a new one - `POST /vacancies`, also quite simple. It would help with testing. Here, the authorization should be considered, as different types of user should be able to create reservations and vacancies.
