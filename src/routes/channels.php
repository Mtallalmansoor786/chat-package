<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channel Routes
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Chat room presence channel authorization
// Channel format: presence-chat-room.{roomId}
Broadcast::channel('chat-room.{roomId}', function ($user, $roomId) {
    // Get the ChatService from the container
    $chatService = app(\ChatPackage\ChatPackage\Services\Contracts\ChatServiceInterface::class);
    
    // Verify user has access to this chat room
    try {
        $chatService->verifyUserAccess((int) $roomId, $user->id);
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    } catch (\ChatPackage\ChatPackage\Exceptions\ChatRoomAccessDeniedException $e) {
        return false;
    }
});

// Private channel for user notifications
// Channel format: private-user.{userId}
Broadcast::channel('user.{userId}', function ($user, $userId) {
    // Only allow users to subscribe to their own notification channel
    return (int) $user->id === (int) $userId;
});

