# Quick Start Guide - Using Chat Package in Another Project

## Fastest Method (5 Minutes)

### 1. Copy Package
```bash
# Copy the entire package folder to your new project
cp -r packages/chat-package /path/to/new-project/packages/
```

### 2. Update composer.json
Add to your `composer.json`:
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/chat-package"
        }
    ],
    "require": {
        "chat-package/chat-package": "dev-main",
        "pusher/pusher-php-server": "^7.0"
    }
}
```

### 3. Install
```bash
composer require chat-package/chat-package
```

### 4. Configure .env
```env
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster
BROADCAST_DRIVER=pusher
```

### 5. Run Migrations
```bash
php artisan migrate
```

### 6. Clear Caches
```bash
php artisan optimize:clear
```

### 7. Access Chat
Go to: `http://your-domain/chat`

## That's It! 🎉

The package is now installed and ready to use. All routes are automatically registered under `/chat` prefix.

## Next Steps

- Create chat rooms at `/chat`
- Add users to rooms
- Start chatting in real-time!

## Need Help?

See `INSTALLATION_OTHER_PROJECTS.md` for detailed instructions and troubleshooting.

