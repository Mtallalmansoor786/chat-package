<?php

namespace ChatPackage\ChatPackage\Repositories;

use ChatPackage\ChatPackage\Models\ChatRoom;
use ChatPackage\ChatPackage\Repositories\Contracts\ChatRoomRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ChatRoomRepository implements ChatRoomRepositoryInterface
{
    /**
     * Find a chat room by ID.
     */
    public function findById(int $id): ?ChatRoom
    {
        return ChatRoom::find($id);
    }

    /**
     * Find a chat room by ID with relationships.
     */
    public function findByIdWithRelations(int $id, array $relations = []): ?ChatRoom
    {
        return ChatRoom::with($relations)->find($id);
    }

    /**
     * Get all chat rooms for a user.
     */
    public function getRoomsForUser(int $userId, array $relations = []): Collection
    {
        try {
            $query = ChatRoom::whereHas('users', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            });

            if (!empty($relations)) {
                $query->with($relations);
            }

            return $query->latest()->get();
        } catch (\Exception $e) {
            // Return empty collection if tables don't exist or query fails
            return new Collection();
        }
    }

    /**
     * Create a new chat room.
     */
    public function create(array $data): ChatRoom
    {
        return ChatRoom::create($data);
    }

    /**
     * Update a chat room.
     */
    public function update(ChatRoom $chatRoom, array $data): bool
    {
        return $chatRoom->update($data);
    }

    /**
     * Delete a chat room.
     */
    public function delete(ChatRoom $chatRoom): bool
    {
        return $chatRoom->delete();
    }

    /**
     * Check if user is member of chat room.
     */
    public function isUserMember(int $roomId, int $userId): bool
    {
        return ChatRoom::whereHas('users', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })->where('id', $roomId)->exists();
    }

    /**
     * Add users to chat room.
     */
    public function addUsers(ChatRoom $chatRoom, array $userIds): void
    {
        $chatRoom->users()->syncWithoutDetaching($userIds);
    }

    /**
     * Remove users from chat room.
     */
    public function removeUsers(ChatRoom $chatRoom, array $userIds): void
    {
        $chatRoom->users()->detach($userIds);
    }
}

