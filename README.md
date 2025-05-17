
# Weather API Project

This is a Laravel-based API designed to manage users and consult climatic data from WeatherAPI. It features user authentication, weather data retrieval, search history, favorite cities, and an admin panel for user management. The API also supports multi-language responses and is documented using Swagger.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Running the Application](#running-the-application)
- [Running Tests](#running-tests)
- [API Usage](#api-usage)
  - [Authentication](#authentication)
  - [Multi-language Support](#multi-language-support)
  - [Swagger Documentation](#swagger-documentation)


## Prerequisites

Before you begin, ensure you have met the following requirements:

*   PHP (version compatible with your Laravel version, e.g., >= 8.1)
*   Composer (PHP dependency manager)
*   A database server (e.g., MySQL, PostgreSQL). This project is configured for MySQL by default.
*   [WeatherAPI.com](https://www.weatherapi.com/) account and API Key.

## Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/vdleon30/test-pulpoline
    cd test-pulpoline
    ```

2.  **Install Composer dependencies:**
    ```bash
    composer install
    ```

3.  **Create your environment file:**
    Copy the example environment file and generate your application key.
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Configure your `.env` file:**
    Open the `.env` file and update the following sections, especially the database credentials and your WeatherAPI key:
    ```ini
    APP_NAME="Laravel Weather API"
    APP_ENV=local
    APP_DEBUG=true
    APP_URL=http://localhost:8000 # Or your preferred local URL

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=test_pulpoline # Or your preferred database name
    DB_USERNAME=root # Your database username
    DB_PASSWORD= # Your database password

    WEATHER_API_KEY=YOUR_WEATHER_API_KEY_HERE
    WEATHER_API_BASE_URL=http://api.weatherapi.com/v1
    ```

5.  **Create the database:**
    Ensure the database specified in your `.env` file (e.g., `test_pulpoline`) exists on your database server.

6.  **Run database migrations:**
    This will create all the necessary tables, including those for users, Sanctum, roles/permissions, search history, and favorites.
    ```bash
    php artisan migrate
    ```

7.  **Run database seeders:**
    This will populate the database with initial roles, permissions, an admin user, and sample users.
    ```bash
    php artisan db:seed
    ```
    *   Default Admin User: `admin@example.com` / `password` (Change this password!)

## Running the Application

To start the Laravel development server:

```bash
php artisan serve
```

The API will typically be available at `http://127.0.0.1:8000/api/`.

## Running Tests

1.  **Setup Test Environment:**
    It's highly recommended to use a separate database for testing. You can configure this in your `phpunit.xml` or by creating a `.env.testing` file. The project is pre-configured in `phpunit.xml` to use an in-memory SQLite database for tests.

2.  **Run all tests:**
    ```bash
    php artisan test
    # OR
    ./vendor/bin/phpunit
    ```

## API Usage

### Authentication

Most API endpoints require authentication using Laravel Sanctum.

1.  **Register:** `POST /api/register` with `name`, `email`, `password`, and `password_confirmation`.
2.  **Login:** `POST /api/login` with `email` and `password`.

Both endpoints will return an API token upon success. This token must be included in the `Authorization` header for subsequent authenticated requests:

```
Authorization: Bearer YOUR_API_TOKEN
```

### Multi-language Support

The API supports multi-language responses for error messages and other system messages. To request a specific language, include the `Accept-Language` header in your request:

```
Accept-Language: es  # For Spanish
Accept-Language: en  # For English (default)
```
Supported languages are currently English (`en`) and Spanish (`es`).

### Swagger Documentation

The API is documented using Swagger (OpenAPI). You can access the interactive Swagger UI at:

**`/api/documentation`** (e.g., `http://127.0.0.1:8000/api/documentation`)

This UI allows you to explore all available endpoints, view their parameters, request/response schemas, and try them out directly.

