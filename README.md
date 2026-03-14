# Cats

*As in "Herding Cats" -- built to be used with [Laravel Herd](https://herd.laravel.com)*

A native macOS menu bar application for managing background services across your development projects. Stop juggling terminal windows -- start, stop, and monitor all your dev services from one place.

Built with [Laravel](https://laravel.com), [Livewire](https://livewire.laravel.com), and [NativePHP](https://nativephp.com).

## Features

- **Menu bar app** -- lives in your macOS menu bar, always one click away
- **Multi-project support** -- organise services by application/project
- **One-click service control** -- start, stop, and restart services instantly
- **Auto-start** -- configure services to launch automatically when the app opens
- **Live log viewer** -- view real-time output from running services with ANSI colour support
- **Drag-to-reorder** -- arrange your applications in whatever order suits you

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm

## Getting Started

Clone the repo and install dependencies:

```bash
git clone https://github.com/your-username/cats.git
cd cats
composer install
npm install
```

Set up your environment and database:

```bash
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
```

## Running in Development

### Native desktop mode (recommended)

This launches the app as a native macOS menu bar application:

```bash
composer native:dev
```

This runs two processes concurrently:
- `php artisan native:serve` -- the NativePHP/Electron desktop app
- `npm run dev` -- Vite dev server for hot-reloading assets

### Web mode

For development without the native wrapper:

```bash
composer dev
```

This starts the Laravel dev server, queue worker, log viewer (Pail), and Vite dev server concurrently. The app will be available at `http://localhost:8000`.

## Running Tests

```bash
composer test
```

This clears the config cache and runs the PHPUnit test suite.

## Building for Production

### 1. Build frontend assets

```bash
npm run build
```

This creates optimised production assets in `public/build/`.

### 2. Build the native binary

NativePHP uses Electron under the hood. To package the app as a distributable binary:

```bash
php artisan native:build
```

This produces a platform-specific binary (`.app` on macOS) in the `dist/` directory.

For build configuration, code signing, and distribution options, see the [NativePHP build documentation](https://nativephp.com/docs/1/getting-started/build).

## Project Structure

```
app/
  Cats/
    MainMenu.php              # Menu bar UI builder
    ServiceManager.php        # Service lifecycle management
  Models/
    Application.php           # Project model (has many services)
    Service.php               # Service/command model
  Providers/
    NativeAppServiceProvider.php  # NativePHP menu bar config

resources/views/livewire/
  menu.blade.php              # Main menu bar UI
  application.blade.php       # Add/edit application form
  log-viewer.blade.php        # Real-time log viewer

database/migrations/          # Database schema
routes/web.php                # Route definitions
```

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 12 |
| Desktop | NativePHP (Electron) |
| UI Components | Livewire 3 + Volt |
| Styling | Tailwind CSS 4 |
| Build Tool | Vite |
| Database | SQLite |
| Log Rendering | ansi_up |

## License

MIT
