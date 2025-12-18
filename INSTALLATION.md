# Installation Guide

## Quick Start

### 1. Install the Package

The package is already configured in your `composer.json`. Just run:

```bash
composer require chat-package/chat-package
```

### 2. Configure Pusher

Add your Pusher credentials to your `.env` file:

```env
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=your_cluster
BROADCAST_DRIVER=pusher
```

### 3. Run Migrations

```bash
php artisan migrate
```

This will create the following tables:
- `chat_rooms` - Stores chat room information
- `chat_room_user` - Pivot table for room members (peers)
- `messages` - Stores chat messages

### 4. Access Chat

Navigate to `/chat` in your browser to access the chat interface.

## Features

✅ **Auto Database Detection** - Automatically uses your parent project's database connection  
✅ **Auto .env Reading** - Reads Pusher configuration from parent project's .env  
✅ **Peer-to-Peer Chat** - Create rooms and chat with multiple users  
✅ **Real-time Messaging** - Powered by Pusher for instant message delivery  
✅ **Vertical Peer Table** - See all peers in each chat room  
✅ **Bootstrap 5 UI** - Modern, responsive design  

## Usage

### Creating a Chat Room

1. Click "Create Room" button
2. Enter room name and description
3. Select users to add to the room
4. Click "Create Room"

### Sending Messages

1. Select a chat room
2. Type your message in the input field
3. Press Enter or click Send
4. Messages appear in real-time for all room members

### Viewing Peers

The right sidebar shows all peers (users) in the current chat room with their online/offline status.

## Package Structure

```
packages/chat-package/
├── src/
│   ├── Http/
│   │   └── Controllers/
│   │       └── ChatController.php
│   ├── Models/
│   │   ├── ChatRoom.php
│   │   └── Message.php
│   ├── Events/
│   │   └── MessageSent.php
│   ├── database/
│   │   └── migrations/
│   ├── resources/
│   │   └── views/
│   ├── routes/
│   │   └── web.php
│   └── config/
│       └── chat-package.php
└── composer.json
```

## Routes

All routes are prefixed with `/chat`:

- `GET /chat` - List all chat rooms
- `GET /chat/room/{roomId}` - View specific chat room
- `POST /chat/room/create` - Create new chat room
- `POST /chat/room/{roomId}/message` - Send message
- `GET /chat/room/{roomId}/messages` - Get messages (API)
- `GET /chat/room/{roomId}/peers` - Get peers (API)

## Configuration

The package automatically reads from your parent project's `.env` file. To customize, publish the config:

```bash
php artisan vendor:publish --tag=chat-package-config
```

Then edit `config/chat-package.php`.

## Troubleshooting

### Package not discovered

Run:
```bash
php artisan package:discover
```

### Migrations not running

Make sure the package is properly installed:
```bash
composer dump-autoload
php artisan migrate
```

### Pusher not working

1. Verify your Pusher credentials in `.env`
2. Check that `BROADCAST_DRIVER=pusher` is set
3. Ensure Pusher JavaScript SDK is loaded (included in views)

