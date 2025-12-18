<?php

namespace ChatPackage\ChatPackage\Repositories\Contracts;

use ChatPackage\ChatPackage\Models\Message;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MessageRepositoryInterface
{
    /**
     * Find a message by ID.
     */
    public function findById(int $id): ?Message;

    /**
     * Get messages for a chat room.
     */
    public function getMessagesForRoom(int $roomId, int $perPage = 50): LengthAwarePaginator;

    /**
     * Get latest messages for a chat room.
     */
    public function getLatestMessagesForRoom(int $roomId, int $limit = 1): \Illuminate\Database\Eloquent\Collection;

    /**
     * Create a new message.
     */
    public function create(array $data): Message;

    /**
     * Update a message.
     */
    public function update(Message $message, array $data): bool;

    /**
     * Delete a message.
     */
    public function delete(Message $message): bool;

    /**
     * Get messages count for a room.
     */
    public function getCountForRoom(int $roomId): int;
}

