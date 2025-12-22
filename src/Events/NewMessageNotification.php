<?php

namespace ChatPackage\ChatPackage\Events;

use ChatPackage\ChatPackage\Models\Message;
use ChatPackage\ChatPackage\Models\ChatRoom;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageNotification implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $chatRoom;
    public $unreadCount;
    public $recipientId;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message, ChatRoom $chatRoom, int $unreadCount, int $recipientId)
    {
        $this->message = $message->load('user');
        $this->chatRoom = $chatRoom;
        $this->unreadCount = $unreadCount;
        $this->recipientId = $recipientId;
    }

    /**
     * Get the channels the event should broadcast on.
     * Broadcasts to the specific recipient's private channel.
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('user.' . $this->recipientId);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'new.message';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'chat_room_id' => $this->message->chat_room_id,
            'chat_room_name' => $this->chatRoom->name,
            'chat_room_display_name' => $this->chatRoom->getDisplayName($this->recipientId),
            'message' => [
                'id' => $this->message->id,
                'user_id' => $this->message->user_id,
                'user' => [
                    'id' => $this->message->user->id,
                    'name' => $this->message->user->name,
                    'email' => $this->message->user->email,
                ],
                'message' => $this->message->message,
                'type' => $this->message->type,
                'created_at' => $this->message->created_at->toDateTimeString(),
            ],
            'unread_count' => $this->unreadCount,
        ];
    }
}

