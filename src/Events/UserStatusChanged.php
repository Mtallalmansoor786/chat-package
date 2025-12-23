<?php

namespace ChatPackage\ChatPackage\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $status; // 'online' or 'offline'
    public $lastSeenAt;
    public $chatRoomIds; // Array of chat room IDs where this user should be notified

    /**
     * Create a new event instance.
     */
    public function __construct(int $userId, string $status, ?string $lastSeenAt = null, array $chatRoomIds = [])
    {
        $this->userId = $userId;
        $this->status = $status;
        $this->lastSeenAt = $lastSeenAt;
        $this->chatRoomIds = $chatRoomIds;
    }

    /**
     * Get the channels the event should broadcast on.
     * Broadcasts to private channels for users in the same chat rooms.
     */
    public function broadcastOn(): array
    {
        $channels = [];
        
        // Get all users in the same chat rooms (except the user whose status changed)
        $chatService = app(\ChatPackage\ChatPackage\Services\Contracts\ChatServiceInterface::class);
        $recipientIds = [];
        
        foreach ($this->chatRoomIds as $roomId) {
            try {
                $peers = $chatService->getPeers($roomId, $this->userId);
                foreach ($peers as $peer) {
                    if ($peer->id != $this->userId && !in_array($peer->id, $recipientIds)) {
                        $recipientIds[] = $peer->id;
                    }
                }
            } catch (\Exception $e) {
                // Skip if room access denied or error
                continue;
            }
        }
        
        // Broadcast to each recipient's private notification channel
        foreach ($recipientIds as $recipientId) {
            $channels[] = new PrivateChannel('user.' . $recipientId);
        }
        
        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'user.status.changed';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'status' => $this->status,
            'last_seen_at' => $this->lastSeenAt,
        ];
    }
}


