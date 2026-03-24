# Laravel Payload Editor & Payload Editor

[![Latest Version on Packagist](https://img.shields.io/packagist/v/php-nl/laravel-payload-editor.svg?style=flat-square)](https://packagist.org/packages/php-nl/laravel-payload-editor)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/php-nl/laravel-payload-editor/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/php-nl/laravel-payload-editor/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/php-nl/laravel-payload-editor.svg?style=flat-square)](https://packagist.org/packages/php-nl/laravel-payload-editor)

Every Laravel developer has been there: A queued job fails because of a silly typo in the payload (like a misspelled email address or an expired third-party ID). You can view the exception and the failed payload in Horizon, but you can only hit "Retry". If the payload is wrong, retrying won't help. 

**Laravel Payload Editor** solves this by providing a beautiful UI to unserialize the failed job, safely edit the primitive properties and Eloquent Model Identifiers, and requeue the job with the updated payload.

## Features

- **Safe Editing:** Unserializes PHP queue objects, reads their properties via Reflection, and ensures type-safety (integers remain integers, booleans remain booleans).
- **Eloquent Support:** Safely edit the `id` of an `Illuminate\Contracts\Database\ModelIdentifier` inside the job so you can point the job to the correct database record without manual DB queries.
- **Beautiful UI:** Built with Livewire 3 and Tailwind CSS, inspired by Laravel Horizon and Pulse.
- **Horizon & Database Supported:** Works out of the box with the default Laravel `failed_jobs` database connection.

## Installation

You can install the package via composer:

```bash
composer require php-nl/laravel-payload-editor
```

Next, publish the configuration file (optional):

```bash
php artisan vendor:publish --tag="laravel-payload-editor-config"
```



## Usage

Simply visit `/laravel-payload-editor` in your local development environment to access the dashboard. 

### Security in Production

By default, the dashboard is only accessible in the `local` environment. For production usage, you must define the `viewLaravelPayloadEditor` gate in your `App\Providers\AppServiceProvider` or `AuthServiceProvider`.

```php
use Illuminate\Support\Facades\Gate;

/**
 * Register any authentication / authorization services.
 */
public function boot(): void
{
    Gate::define('viewLaravelPayloadEditor', function ($user) {
        return in_array($user->email, [
            'admin@yourdomain.com',
        ]);
    });
}
```

## Testing

```bash
composer test
```

## Credits

- [PHP.nl](https://php.nl)
- [Serff Webdevelopment](https://serff-webdevelopment.nl)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
