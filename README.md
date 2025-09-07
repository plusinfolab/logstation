# LogStation

[![Latest Version on Packagist](https://img.shields.io/packagist/v/plusinfolab/logstation.svg?style=flat-square)](https://packagist.org/packages/plusinfolab/logstation)
[![Total Downloads](https://img.shields.io/packagist/dt/plusinfolab/logstation.svg?style=flat-square)](https://packagist.org/packages/plusinfolab/logstation)

An elegant log management assistant for Laravel applications. Search, filter, export and monitor logs in real-time with a beautiful, Telescope-inspired UI.

## Features

- 🔍 **Advanced Search & Filtering** - Search logs by message, level, channel, date range, tags, and user
- 📤 **Export Logs** - Export to JSON, CSV, or TXT formats
- 📋 **Saved Snippets** - Save and reuse complex search filters
- 🔐 **Authorization Gates** - Customizable access control
- ⚡ **Real-time Viewing** - See logs appear instantly (with broadcasting)
- 🎨 **Beautiful UI** - Telescope-inspired interface built with Vue 3 and Tailwind CSS
- 💾 **Flexible Storage** - Database or file-based storage
- 🏷️ **Auto-tagging** - Automatic tagging by user, request, session, and IP
- 🔥 **Exception Tracking** - Full stack traces and exception details
- 📊 **Statistics Dashboard** - Overview of logs by level, channel, and time period

## Installation

Install the package via composer:

```bash
composer require plusinfolab/logstation
```

Run the installation command:

```bash
php artisan logstation:install
```

This will:
- Publish the configuration file
- Publish and run migrations
- Publish frontend assets

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=logstation-config
```

The config file allows you to customize:
- Storage driver (database or file)
- Database connection
- Data retention
- Watchers (log, exception, query)
- Authorization
- Real-time broadcasting
- And more...

### Authorization

By default, LogStation is only accessible in the `local` environment. To customize who can access LogStation, publish the config file and modify the `authorization.callback`:

```php
// config/logstation.php

'authorization' => [
    'callback' => function ($request) {
        // Example 1: Allow specific users
        return in_array($request->user()?->email, [
            'admin@example.com',
            'developer@example.com',
        ]);
        
        // Example 2: Use Laravel Gate
        return Gate::allows('viewLogstation', $request->user());
        
        // Example 3: Check user role
        return $request->user()?->hasRole('admin');
        
        // Example 4: Environment-based
        return app()->environment(['local', 'staging']);
    },
],
```

You can also define a Gate in your `AuthServiceProvider`:

```php
use Illuminate\Support\Facades\Gate;

Gate::define('viewLogstation', function ($user) {
    return in_array($user->email, [
        'admin@example.com',
    ]);
});
```

Or use a Policy:

```bash
php artisan make:policy LogStationPolicy
```

```php
// app/Policies/LogStationPolicy.php
public function view(?User $user): bool
{
    return $user && $user->isAdmin();
}
```

### Enable/Disable

```php
'enabled' => env('LOGSTATION_ENABLED', !app()->environment('production')),
```

### Storage Driver

Choose between `database` (default) or `file`:

```php
'driver' => env('LOGSTATION_DRIVER', 'database'),
```

### Database Connection

Use a separate database connection (recommended):

```php
'database' => [
    'connection' => env('LOGSTATION_DB_CONNECTION', config('database.default')),
],
```

In your `.env`:

```env
LOGSTATION_DB_CONNECTION=logstation
```

Then add the connection in `config/database.php`:

```php
'connections' => [
    'logstation' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'database' => 'logstation',
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD'),
        // ... other settings
    ],
],
```

### Data Retention

Configure how long to keep logs:

```php
'retention' => [
    'days' => env('LOGSTATION_RETENTION_DAYS', 7),
],
```

### Watchers

Enable/disable specific watchers:

```php
'watchers' => [
    PlusinfoLab\Logstation\Watchers\LogWatcher::class => [
        'enabled' => true,
        'channels' => ['stack', 'single', 'daily'],
        'levels' => ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'],
    ],
    PlusinfoLab\Logstation\Watchers\ExceptionWatcher::class => [
        'enabled' => true,
    ],
    PlusinfoLab\Logstation\Watchers\QueryWatcher::class => [
        'enabled' => false,
        'slow' => 100, // Log queries slower than 100ms
    ],
],
```

### Authorization

Customize who can access LogStation:

```php
'authorization' => [
    'callback' => function ($request) {
        return app()->environment('local') ||
               $request->user()?->email === env('LOGSTATION_ADMIN_EMAIL');
    },
],
```

Or use a Gate:

```php
Gate::define('viewLogstation', function ($user) {
    return in_array($user->email, [
        'admin@example.com',
    ]);
});
```

### Real-time Broadcasting

Enable real-time log updates:

```php
'broadcasting' => [
    'enabled' => env('LOGSTATION_BROADCASTING', false),
    'channel' => 'logstation',
],
```

## Usage

### Accessing the Dashboard

Visit `/logstation` in your browser (configurable via `LOGSTATION_PATH`).

### Programmatic Usage

```php
use PlusinfoLab\Logstation\Facades\Logstation;

// Record a log entry
Logstation::recordLog([
    'level_name' => 'error',
    'message' => 'Something went wrong',
    'context' => ['user_id' => 123],
]);

// Search logs
$logs = Logstation::search([
    'level' => 'error',
    'start_date' => now()->subDays(7),
    'search' => 'payment',
], perPage: 50);

// Get statistics
$stats = Logstation::getStatistics();

// Find specific entry
$entry = Logstation::find($id);

// Delete entry
Logstation::delete($id);
```

### Artisan Commands

```bash
# Install LogStation
php artisan logstation:install

# Prune old entries (based on retention config)
php artisan logstation:prune

# Prune entries older than specific days
php artisan logstation:prune --days=30

# Clear all entries
php artisan logstation:clear

# Publish assets
php artisan logstation:publish
```

Schedule automatic pruning in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('logstation:prune')->daily();
}
```

## Search & Filtering

LogStation provides powerful filtering capabilities:

- **By Level**: Filter by log level (emergency, alert, critical, error, warning, notice, info, debug)
- **By Channel**: Filter by log channel
- **By Date Range**: Filter logs within a specific date range
- **By User**: Filter logs by authenticated user
- **By Tag**: Filter by auto-generated or custom tags
- **Full-text Search**: Search in message, exception message, and context

### Creating Snippets

Save frequently used filters as snippets:

1. Apply your desired filters in the UI
2. Click "Save as Snippet"
3. Give it a name and description
4. Reuse anytime from the Snippets menu

## Exporting Logs

Export filtered logs in multiple formats:

1. Apply your filters
2. Click "Export"
3. Choose format (JSON, CSV, TXT)
4. Download the file

Exports are limited to 10,000 entries by default (configurable).

## Performance Considerations

### Use a Separate Database

For production applications, use a dedicated database connection:

```env
LOGSTATION_DB_CONNECTION=logstation
```

This prevents LogStation from impacting your main application's performance.

### Disable in Production

By default, LogStation is disabled in production. Enable only when needed:

```env
LOGSTATION_ENABLED=true
```

### Regular Pruning

Schedule the prune command to run daily:

```php
$schedule->command('logstation:prune')->daily();
```

### Disable Unnecessary Watchers

Disable watchers you don't need:

```php
'watchers' => [
    PlusinfoLab\Logstation\Watchers\QueryWatcher::class => [
        'enabled' => false, // Disable query watcher
    ],
],
```

## Frontend Development

The UI is built with Vue 3, Inertia.js, and Tailwind CSS. To customize:

```bash
# Install dependencies
npm install

# Build for production
npm run build

# Watch for changes
npm run watch
```

## Comparison with Other Tools

| Feature | LogStation | Laravel Telescope | Laravel Log Viewer |
|---------|-----------|-------------------|-------------------|
| Log Search | ✅ | ❌ | ✅ |
| Log Export | ✅ | ❌ | ✅ |
| Saved Snippets | ✅ | ❌ | ❌ |
| Real-time Viewing | ✅ | ✅ | ❌ |
| Exception Tracking | ✅ | ✅ | ✅ |
| Database Queries | ✅ | ✅ | ❌ |
| Request Tracking | ✅ | ✅ | ❌ |
| Beautiful UI | ✅ | ✅ | ⚠️ |
| File Storage | ✅ | ❌ | ✅ |

## Security

LogStation is designed for development and debugging. **Never expose it publicly in production** without proper authentication.

Default security measures:
- Only accessible in local environment by default
- Customizable authorization callback
- Gate-based access control
- Configurable middleware

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Aditya](https://github.com/plusinfolab)
- Inspired by [Laravel Telescope](https://github.com/laravel/telescope)
- Built with [Spatie's Laravel Package Tools](https://github.com/spatie/laravel-package-tools)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
