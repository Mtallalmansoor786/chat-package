<?php

namespace ChatPackage\ChatPackage\Services\Contracts;

use ChatPackage\ChatPackage\Models\ChatRoom;
use ChatPackage\ChatPackage\Models\Message;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ChatServiceInterface
{
    /**
     * Get all chat rooms for authenticated user.
     */
    public function getUserChatRooms(int $userId): Collection;

    /**
     * Get a chat room with messages and users.
     */
    public function getChatRoom(int $roomId, int $userId): ChatRoom;

    /**
     * Create a new chat room.
     */
    public function createChatRoom(array $data, int $userId): ChatRoom;

    /**
     * Send a message to a chat room.
     */
    public function sendMessage(int $roomId, int $userId, string $message): Message;

    /**
     * Get messages for a chat room.
     */
    public function getMessages(int $roomId, int $userId, int $perPage = 50): LengthAwarePaginator;

    /**
     * Get peers (users) for a chat room.
     */
    public function getPeers(int $roomId, int $userId): Collection;

    /**
     * Verify user has access to chat room.
     */
    public function verifyUserAccess(int $roomId, int $userId): bool;

    /**
     * Get unread message counts for chat rooms.
     */
    public function getUnreadCounts(int $userId, Collection $chatRooms): array;

    /**
     * Mark all messages in a room as read for a user.
     */
    public function markMessagesAsRead(int $roomId, int $userId): void;

    /**
     * Get the first unread message ID in a room for a user.
     */
    public function getFirstUnreadMessageId(int $roomId, int $userId): ?int;
}

