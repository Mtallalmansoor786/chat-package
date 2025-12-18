# Chat Package for Laravel

A peer-to-peer chat package for Laravel with real-time messaging using Pusher. This package provides a complete chat solution with chat rooms, peer management, and real-time message broadcasting.

## Features

- ✅ Peer-to-peer chat rooms
- ✅ Real-time messaging with Pusher
- ✅ Vertical peer table for each chat room
- ✅ Bootstrap 5 UI
- ✅ Auto-detects parent project's database connection
- ✅ Reads Pusher configuration from parent project's .env file
- ✅ Composer-based installation
- ✅ **SOLID Principles** - Follows all SOLID principles for maintainable code
- ✅ **Repository Pattern** - Clean separation of data access and business logic
- ✅ **Service Layer** - Business logic separated from controllers
- ✅ **Dependency Injection** - Interfaces and implementations properly bound

## Installation

### Method 1: Composer Require (Recommended - After Publishing)

Once published to Packagist or a Git repository:

```bash
composer require mtallalmansoor786/chat-package
```

Then:
1. Add Pusher credentials to `.env`
2. Run `php artisan migrate`
3. Access `/chat`

**That's it!** Works like any other Composer package.

### Method 2: Local Development (Current Project)

This package is already installed in the current project. See `INSTALLATION.md` for details.

### Method 3: Copy Package (Before Publishing)

To use this package in a **different Laravel project** before publishing:

- **[QUICK_START.md](QUICK_START.md)** - Fast 5-minute setup guide
- **[INSTALLATION_OTHER_PROJECTS.md](INSTALLATION_OTHER_PROJECTS.md)** - Detailed installation guide

---

## Publishing to Composer

Want to make it installable via `composer require`? See:

- **[SETUP_FOR_COMPOSER.md](SETUP_FOR_COMPOSER.md)** - Quick 5-minute setup guide
- **[PUBLISH_TO_COMPOSER.md](PUBLISH_TO_COMPOSER.md)** - Complete publishing guide (Packagist, Private Repos, etc.)

**Quick Steps:**
1. Push package to GitHub
2. Submit to Packagist (or use private repo)
3. Install anywhere with: `composer require mtallalmansoor786/chat-package`

---

## Installation (Current Project)

### Step 1: Install via Composer

Add the package to your Laravel project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/chat-package"
        }
    ],
    "require": {
        "chat-package/chat-package": "*"
    }
}
```

Then run:

```bash
composer require mtallalmansoor786/chat-package
```

### Step 2: Configure Pusher

Add your Pusher credentials to your `.env` file:

```env
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster
BROADCAST_DRIVER=pusher
```

### Step 3: Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=chat-package-config
```

### Step 4: Run Migrations

The package will automatically use your parent project's database connection. Run migrations:

```bash
php artisan migrate
```

### Step 5: Install Pusher PHP SDK (if not already installed)

```bash
composer require pusher/pusher-php-server
```

## Usage

### Access Chat Interface

Once installed, you can access the chat interface at:

```
/chat
```

### Create Chat Rooms

Users can create chat rooms and add peers. Each chat room displays:
- Messages in real-time
- Vertical table of peers (users) in the room
- Online/offline status

### API Routes

The package provides the following routes (all prefixed with `/chat`):

- `GET /chat` - Chat rooms list
- `GET /chat/room/{roomId}` - View specific chat room
- `POST /chat/room/create` - Create new chat room
- `POST /chat/room/{roomId}/message` - Send message
- `GET /chat/room/{roomId}/messages` - Get messages (API)
- `GET /chat/room/{roomId}/peers` - Get peers (API)

## Configuration

The package automatically reads Pusher configuration from your parent project's `.env` file. No additional configuration is needed unless you want to customize:

```php
// config/chat-package.php
return [
    'pusher' => [
        'app_id' => env('PUSHER_APP_ID'),
        'key' => env('PUSHER_APP_KEY'),
        'secret' => env('PUSHER_APP_SECRET'),
        'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
        'useTLS' => true,
    ],
    'chat' => [
        'per_page' => 50,
        'max_message_length' => 1000,
    ],
];
```

## Database Tables

The package creates the following tables:

- `chat_rooms` - Stores chat room information
- `chat_room_user` - Pivot table for room members (peers)
- `messages` - Stores chat messages

All tables use your parent project's default database connection automatically.

## Architecture

This package follows **SOLID principles** and implements the **Repository Pattern**:

- **Single Responsibility**: Each class has one clear purpose
- **Open/Closed**: Open for extension, closed for modification
- **Liskov Substitution**: Interfaces can be swapped with implementations
- **Interface Segregation**: Focused, client-specific interfaces
- **Dependency Inversion**: Depend on abstractions, not concretions

### Code Structure

```
src/
├── Http/
│   ├── Controllers/          # HTTP request handling
│   └── Requests/             # Form validation
├── Repositories/
│   ├── Contracts/             # Repository interfaces
│   └── [Repository].php       # Repository implementations
├── Services/
│   ├── Contracts/             # Service interfaces
│   └── ChatService.php       # Business logic
├── Models/                    # Eloquent models
└── Exceptions/               # Custom exceptions
```

See [SOLID_PRINCIPLES.md](SOLID_PRINCIPLES.md) for detailed documentation.

## Requirements

- Laravel 12+
- PHP 8.2+
- Pusher account and credentials
- Bootstrap 5 (for UI)

## License

MIT

