<?php

namespace ChatPackage\ChatPackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    /**
     * The connection name for the model.
     * Uses parent project's default database connection.
     */
    protected $connection = null;

    protected $table = 'messages';

    protected $fillable = [
        'chat_room_id',
        'user_id',
        'message',
        'type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the chat room that owns the message.
     */
    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }

    /**
     * Get the user that sent the message.
     */
    public function user()
    {
        $userModel = config('auth.providers.users.model', \App\Models\User::class);
        return $this->belongsTo($userModel);
    }
}

