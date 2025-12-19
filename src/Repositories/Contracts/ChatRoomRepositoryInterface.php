<?php

namespace ChatPackage\ChatPackage\Repositories\Contracts;

use ChatPackage\ChatPackage\Models\ChatRoom;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ChatRoomRepositoryInterface
{
    /**
     * Find a chat room by ID.
     */
    public function findById(int $id): ?ChatRoom;

    /**
     * Find a chat room by ID with relationships.
     */
    public function findByIdWithRelations(int $id, array $relations = []): ?ChatRoom;

    /**
     * Get all chat rooms for a user.
     */
    public function getRoomsForUser(int $userId, array $relations = []): Collection;

    /**
     * Create a new chat room.
     */
    public function create(array $data): ChatRoom;

    /**
     * Update a chat room.
     */
    public function update(ChatRoom $chatRoom, array $data): bool;

    /**
     * Delete a chat room.
     */
    public function delete(ChatRoom $chatRoom): bool;

    /**
     * Check if user is member of chat room.
     */
    public function isUserMember(int $roomId, int $userId): bool;

    /**
     * Add users to chat room.
     */
    public function addUsers(ChatRoom $chatRoom, array $userIds): void;

    /**
     * Remove users from chat room.
     */
    public function removeUsers(ChatRoom $chatRoom, array $userIds): void;

    /**
     * Find or create a peer-to-peer chat room between two users.
     */
    public function findOrCreatePeerToPeerChat(int $userId1, int $userId2): ChatRoom;

    /**
     * Find an existing peer-to-peer chat room between two users.
     * Returns null if no chat exists.
     */
    public function findPeerToPeerChat(int $userId1, int $userId2): ?ChatRoom;
}

