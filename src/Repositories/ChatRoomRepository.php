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

    /**
     * Find or create a peer-to-peer chat room between two users.
     * Returns existing room if found, creates new one if not.
     */
    public function findOrCreatePeerToPeerChat(int $userId1, int $userId2): ChatRoom
    {
        // Find all rooms where both users are members
        $rooms = ChatRoom::whereHas('users', function ($query) use ($userId1) {
            $query->where('users.id', $userId1);
        })
        ->whereHas('users', function ($query) use ($userId2) {
            $query->where('users.id', $userId2);
        })
        ->with('users')
        ->get();

        // Filter to find P2P chats (exactly 2 users)
        $existingRoom = $rooms->filter(function ($room) use ($userId1, $userId2) {
            // Check if room has exactly 2 users and both are our target users
            $roomUserIds = $room->users->pluck('id')->toArray();
            return count($roomUserIds) === 2 
                && in_array($userId1, $roomUserIds) 
                && in_array($userId2, $roomUserIds);
        })->first();

        if ($existingRoom) {
            return $existingRoom->load('users');
        }

        // Create new P2P chat room
        $userModel = config('auth.providers.users.model', \App\Models\User::class);
        $otherUser = $userModel::find($userId2);

        // Generate a default name (will be overridden by display name logic)
        $roomName = 'Chat with ' . ($otherUser ? $otherUser->name : 'User');

        $chatRoom = $this->create([
            'name' => $roomName,
            'description' => null,
            'created_by' => $userId1,
        ]);

        // Add both users to the room
        $this->addUsers($chatRoom, [$userId1, $userId2]);

        return $chatRoom->load('users');
    }

    /**
     * Find an existing peer-to-peer chat room between two users.
     * Returns null if no chat exists.
     */
    public function findPeerToPeerChat(int $userId1, int $userId2): ?ChatRoom
    {
        // Find all rooms where both users are members
        $rooms = ChatRoom::whereHas('users', function ($query) use ($userId1) {
            $query->where('users.id', $userId1);
        })
        ->whereHas('users', function ($query) use ($userId2) {
            $query->where('users.id', $userId2);
        })
        ->with('users')
        ->get();

        // Filter to find P2P chats (exactly 2 users)
        return $rooms->filter(function ($room) use ($userId1, $userId2) {
            // Check if room has exactly 2 users and both are our target users
            $roomUserIds = $room->users->pluck('id')->toArray();
            return count($roomUserIds) === 2 
                && in_array($userId1, $roomUserIds) 
                && in_array($userId2, $roomUserIds);
        })->first();
    }
}

