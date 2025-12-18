# Installing Chat Package in Another Laravel Project

This guide explains how to use the chat package in a different Laravel project.

## Method 1: Copy Package to New Project (Recommended for Development)

### Step 1: Copy the Package

Copy the entire `packages/chat-package` folder to your new Laravel project:

```bash
# From your current project
cp -r packages/chat-package /path/to/new-project/packages/chat-package

# Or on Windows
xcopy packages\chat-package C:\path\to\new-project\packages\chat-package /E /I
```

### Step 2: Update composer.json

Add the package repository and requirement to your new project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/chat-package"
        }
    ],
    "require": {
        "php": "^8.2",
        "laravel/framework": "^12.0",
        "chat-package/chat-package": "dev-main",
        "pusher/pusher-php-server": "^7.0"
    }
}
```

### Step 3: Install the Package

```bash
composer require chat-package/chat-package
```

### Step 4: Configure Pusher

Add Pusher credentials to your `.env` file:

```env
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster
BROADCAST_DRIVER=pusher
```

### Step 5: Run Migrations

```bash
php artisan migrate
```

### Step 6: Clear Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Step 7: Access Chat

Navigate to `/chat` in your browser or add a link in your navigation.

---

## Method 2: Git Submodule (For Version Control)

### Step 1: Add as Git Submodule

If the package is in a Git repository:

```bash
cd /path/to/new-project
git submodule add <repository-url> packages/chat-package
```

### Step 2-7: Follow Steps 2-7 from Method 1

---

## Method 3: Private Composer Repository (For Team/Production)

### Step 1: Create Git Repository

Push the package to a Git repository (GitHub, GitLab, Bitbucket, etc.)

### Step 2: Add Repository to composer.json

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/yourusername/chat-package.git"
        }
    ],
    "require": {
        "chat-package/chat-package": "dev-main"
    }
}
```

### Step 3: Install

```bash
composer require chat-package/chat-package
```

### Step 4-7: Follow Steps 4-7 from Method 1

---

## Method 4: Packagist (Public Package)

### Step 1: Publish to Packagist

1. Push package to a public Git repository
2. Submit to https://packagist.org
3. Wait for approval

### Step 2: Install via Packagist

```bash
composer require chat-package/chat-package
```

### Step 3-7: Follow Steps 4-7 from Method 1

---

## Requirements Checklist

Before installing, ensure your new project has:

- ✅ Laravel 12+
- ✅ PHP 8.2+
- ✅ MySQL/MariaDB database configured
- ✅ Bootstrap 5 (for UI - already included via CDN)
- ✅ Pusher account and credentials
- ✅ Authentication system (Laravel Breeze/Jetstream or custom)

## Post-Installation Steps

### 1. Verify Package Discovery

```bash
php artisan package:discover
```

You should see:
```
chat-package/chat-package ............................................. DONE
```

### 2. Check Routes

```bash
php artisan route:list --path=chat
```

You should see all chat routes registered.

### 3. Verify Database Tables

```bash
php artisan migrate:status
```

You should see:
- `2025_12_18_213516_create_chat_rooms_table`
- `2025_12_18_213517_create_chat_room_user_table`
- `2025_12_18_213518_create_messages_table`

### 4. Test the Installation

1. Login to your application
2. Navigate to `/chat`
3. You should see the chat interface

## Customization

### Custom User Model

If your project uses a different User model, update the config:

```php
// config/auth.php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\YourUserModel::class,
    ],
],
```

The package automatically detects the User model from your auth config.

### Custom Layout

The package uses `layouts.app` by default. If you use a different layout:

1. Create the same layout structure, OR
2. Publish and modify the views:

```bash
php artisan vendor:publish --tag=chat-package-views
```

### Custom Configuration

Publish the config file to customize:

```bash
php artisan vendor:publish --tag=chat-package-config
```

Then edit `config/chat-package.php`.

## Troubleshooting

### Package Not Discovered

```bash
composer dump-autoload
php artisan package:discover
```

### Routes Not Working

```bash
php artisan route:clear
php artisan route:cache
```

### Views Not Found

```bash
php artisan view:clear
```

### Database Issues

```bash
php artisan migrate:fresh
php artisan migrate
```

### Dependency Issues

```bash
composer update chat-package/chat-package
```

## Package Structure in New Project

After installation, your project structure should look like:

```
your-project/
├── packages/
│   └── chat-package/          # Package files
├── vendor/
│   └── chat-package/
│       └── chat-package/       # Symlinked package
├── database/
│   └── migrations/
│       └── (chat migrations will run automatically)
└── config/
    └── chat-package.php       # If published
```

## Notes

- The package automatically uses your project's database connection
- The package reads Pusher config from your project's `.env` file
- All routes are prefixed with `/chat`
- All routes require authentication (`auth` middleware)
- The package follows SOLID principles and uses Repository Pattern

## Support

If you encounter issues:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check package discovery: `php artisan package:discover`
3. Verify composer autoload: `composer dump-autoload`
4. Clear all caches: `php artisan optimize:clear`

