<?php

namespace ChatPackage\ChatPackage\Services;

use ChatPackage\ChatPackage\Events\MessageSent;
use ChatPackage\ChatPackage\Exceptions\ChatRoomAccessDeniedException;
use ChatPackage\ChatPackage\Repositories\Contracts\ChatRoomRepositoryInterface;
use ChatPackage\ChatPackage\Repositories\Contracts\MessageRepositoryInterface;
use ChatPackage\ChatPackage\Services\Contracts\ChatServiceInterface;
use ChatPackage\ChatPackage\Models\ChatRoom;
use ChatPackage\ChatPackage\Models\Message;
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

        // Broadcast message via Pusher
        event(new MessageSent($message));

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
}

