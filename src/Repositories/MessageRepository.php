<?php

namespace ChatPackage\ChatPackage\Repositories;

use ChatPackage\ChatPackage\Models\Message;
use ChatPackage\ChatPackage\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MessageRepository implements MessageRepositoryInterface
{
    /**
     * Find a message by ID.
     */
    public function findById(int $id): ?Message
    {
        return Message::find($id);
    }

    /**
     * Get messages for a chat room.
     */
    public function getMessagesForRoom(int $roomId, int $perPage = 50): LengthAwarePaginator
    {
        return Message::where('chat_room_id', $roomId)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get latest messages for a chat room.
     */
    public function getLatestMessagesForRoom(int $roomId, int $limit = 1): \Illuminate\Database\Eloquent\Collection
    {
        return Message::where('chat_room_id', $roomId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Create a new message.
     */
    public function create(array $data): Message
    {
        return Message::create($data);
    }

    /**
     * Update a message.
     */
    public function update(Message $message, array $data): bool
    {
        return $message->update($data);
    }

    /**
     * Delete a message.
     */
    public function delete(Message $message): bool
    {
        return $message->delete();
    }

    /**
     * Get messages count for a room.
     */
    public function getCountForRoom(int $roomId): int
    {
        return Message::where('chat_room_id', $roomId)->count();
    }
}

