<?php

namespace ChatPackage\ChatPackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ChatRoom extends Model
{
    /**
     * The connection name for the model.
     * Uses parent project's default database connection.
     */
    protected $connection = null;

    protected $table = 'chat_rooms';

    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the messages for the chat room.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the users (peers) in the chat room.
     */
    public function users(): BelongsToMany
    {
        $userModel = config('auth.providers.users.model', \App\Models\User::class);
        return $this->belongsToMany($userModel, 'chat_room_user', 'chat_room_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Get the creator of the chat room.
     */
    public function creator()
    {
        $userModel = config('auth.providers.users.model', \App\Models\User::class);
        return $this->belongsTo($userModel, 'created_by');
    }
}

