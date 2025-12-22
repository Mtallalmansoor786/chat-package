<?php

namespace ChatPackage\ChatPackage\Services;

use ChatPackage\ChatPackage\Events\MessageSent;
use ChatPackage\ChatPackage\Events\NewMessageNotification;
use ChatPackage\ChatPackage\Exceptions\ChatRoomAccessDeniedException;
use ChatPackage\ChatPackage\Repositories\Contracts\ChatRoomRepositoryInterface;
use ChatPackage\ChatPackage\Repositories\Contracts\MessageRepositoryInterface;
use ChatPackage\ChatPackage\Services\Contracts\ChatServiceInterface;
use ChatPackage\ChatPackage\Models\ChatRoom;
use ChatPackage\ChatPackage\Models\Message;
use ChatPackage\ChatPackage\Models\MessageRead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ChatService implements ChatServiceInterface
{
    public function __construct(
        private ChatRoomRepositoryInterface $chatRoomRepository,
        private MessageRepositoryInterface $messageRepository
    ) {
    }

    /**
     * Get all chat rooms for authenticated user.
     */
    public function getUserChatRooms(int $userId): Collection
    {
        try {
            return $this->chatRoomRepository->getRoomsForUser(
                $userId,
                ['users', 'messages' => function ($query) {
                    $query->latest()->limit(1);
                }]
            );
        } catch (\Exception $e) {
            // Return empty collection if there's an error (e.g., tables don't exist yet)
            return new Collection();
        }
    }

    /**
     * Get a chat room with messages and users.
     */
    public function getChatRoom(int $roomId, int $userId): ChatRoom
    {
        $this->verifyUserAccess($roomId, $userId);

        return $this->chatRoomRepository->findByIdWithRelations(
            $roomId,
            ['users', 'messages.user']
        );
    }

    /**
     * Create a new chat room.
     */
    public function createChatRoom(array $data, int $userId): ChatRoom
    {
        $userIds = $data['user_ids'] ?? [];
        unset($data['user_ids']);

        $data['created_by'] = $userId;
        $chatRoom = $this->chatRoomRepository->create($data);

        // Add creator and selected users to the room
        $allUserIds = array_unique(array_merge($userIds, [$userId]));
        $this->chatRoomRepository->addUsers($chatRoom, $allUserIds);

        return $chatRoom->load('users');
    }

    /**
     * Send a message to a chat room.
     */
    public function sendMessage(int $roomId, int $userId, string $message): Message
    {
        $this->verifyUserAccess($roomId, $userId);

        $messageData = [
            'chat_room_id' => $roomId,
            'user_id' => $userId,
            'message' => $message,
            'type' => 'text',
        ];

        $message = $this->messageRepository->create($messageData);

        // Load chat room with users for notifications
        $chatRoom = $this->chatRoomRepository->findByIdWithRelations($roomId, ['users']);

        // Broadcast message to chat room channel (for active viewers)
        event(new MessageSent($message));

        // Send notification to all recipients (except sender)
        // Each recipient will receive notification on their private channel
        $recipients = $chatRoom->users->where('id', '!=', $userId);
        foreach ($recipients as $recipient) {
            // Calculate unread count for this specific recipient
            $readMessageIds = MessageRead::where('user_id', $recipient->id)
                ->pluck('message_id')
                ->toArray();
            
            $unreadCount = Message::where('chat_room_id', $roomId)
                ->where('user_id', '!=', $recipient->id)
                ->whereNotIn('id', $readMessageIds)
                ->count();
            
            // Include the new message in unread count
            $unreadCount++;
            
            // Fire separate notification event for this recipient with their specific unread count
            // This ensures each user gets their accurate unread count
            event(new NewMessageNotification($message, $chatRoom, $unreadCount, $recipient->id));
        }

        return $message->load('user');
    }

    /**
     * Get messages for a chat room.
     */
    public function getMessages(int $roomId, int $userId, int $perPage = 50): LengthAwarePaginator
    {
        $this->verifyUserAccess($roomId, $userId);

        return $this->messageRepository->getMessagesForRoom($roomId, $perPage);
    }

    /**
     * Get peers (users) for a chat room.
     */
    public function getPeers(int $roomId, int $userId): Collection
    {
        $this->verifyUserAccess($roomId, $userId);

        $chatRoom = $this->chatRoomRepository->findByIdWithRelations($roomId, ['users']);

        return $chatRoom->users;
    }

    /**
     * Verify user has access to chat room.
     *
     * @throws ChatRoomAccessDeniedException
     */
    public function verifyUserAccess(int $roomId, int $userId): bool
    {
        if (!$this->chatRoomRepository->isUserMember($roomId, $userId)) {
            throw new ChatRoomAccessDeniedException('You do not have access to this chat room.');
        }

        return true;
    }

    /**
     * Get unread message counts for chat rooms.
     * Counts messages from other users that haven't been read by the current user.
     */
    public function getUnreadCounts(int $userId, Collection $chatRooms): array
    {
        $unreadCounts = [];

        foreach ($chatRooms as $room) {
            // Get all message IDs that have been read by this user
            $readMessageIds = MessageRead::where('user_id', $userId)
                ->pluck('message_id')
                ->toArray();

            // Count unread messages (from other users, not read by current user)
            $unreadCounts[$room->id] = Message::where('chat_room_id', $room->id)
                ->where('user_id', '!=', $userId)
                ->whereNotIn('id', $readMessageIds)
                ->count();
        }

        return $unreadCounts;
    }

    /**
     * Mark all messages in a room as read for a user.
     */
    public function markMessagesAsRead(int $roomId, int $userId): void
    {
        // Get all unread messages in this room (from other users)
        $unreadMessages = Message::where('chat_room_id', $roomId)
            ->where('user_id', '!=', $userId)
            ->whereDoesntHave('reads', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->get();

        // Mark them as read
        foreach ($unreadMessages as $message) {
            MessageRead::firstOrCreate([
                'message_id' => $message->id,
                'user_id' => $userId,
            ], [
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Get the first unread message ID in a room for a user.
     */
    public function getFirstUnreadMessageId(int $roomId, int $userId): ?int
    {
        $readMessageIds = MessageRead::where('user_id', $userId)
            ->pluck('message_id')
            ->toArray();

        $firstUnread = Message::where('chat_room_id', $roomId)
            ->where('user_id', '!=', $userId)
            ->whereNotIn('id', $readMessageIds)
            ->orderBy('created_at', 'asc')
            ->first();

        return $firstUnread ? $firstUnread->id : null;
    }

    /**
     * Find or create a peer-to-peer chat room between two users.
     * Returns existing room if found, creates new one if not.
     */
    public function findOrCreatePeerToPeerChat(int $currentUserId, int $otherUserId): ChatRoom
    {
        // Prevent user from chatting with themselves
        if ($currentUserId === $otherUserId) {
            throw new \InvalidArgumentException('Cannot create a chat with yourself.');
        }

        return $this->chatRoomRepository->findOrCreatePeerToPeerChat($currentUserId, $otherUserId);
    }

    /**
     * Find an existing peer-to-peer chat room between two users.
     * Returns null if no chat exists.
     */
    public function findPeerToPeerChat(int $currentUserId, int $otherUserId): ?ChatRoom
    {
        // Prevent user from chatting with themselves
        if ($currentUserId === $otherUserId) {
            return null;
        }

        return $this->chatRoomRepository->findPeerToPeerChat($currentUserId, $otherUserId);
    }
}

