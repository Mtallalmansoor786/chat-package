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

    /**
     * Check if this is a peer-to-peer chat (exactly 2 users).
     */
    public function isPeerToPeer(): bool
    {
        // Check if users relationship is already loaded
        if ($this->relationLoaded('users')) {
            return $this->users->count() === 2;
        }
        
        // Otherwise, query the database
        return $this->users()->count() === 2;
    }

    /**
     * Get the other peer in a P2P chat.
     * Returns null if not a P2P chat or user not found.
     */
    public function getOtherPeer(int $currentUserId)
    {
        if (!$this->isPeerToPeer()) {
            return null;
        }

        // Check if users relationship is already loaded
        if ($this->relationLoaded('users')) {
            return $this->users->firstWhere('id', '!=', $currentUserId);
        }

        // Otherwise, query the database
        return $this->users()->where('users.id', '!=', $currentUserId)->first();
    }

    /**
     * Get the display name for the chat room.
     * For P2P chats, returns the other peer's first and last name.
     * For group chats, returns the room name.
     */
    public function getDisplayName(int $currentUserId): string
    {
        if ($this->isPeerToPeer()) {
            $otherPeer = $this->getOtherPeer($currentUserId);
            
            if ($otherPeer) {
                return $this->formatUserName($otherPeer);
            }
        }

        return $this->name;
    }

    /**
     * Format user name as "First Last" or fallback to full name.
     * Attempts to extract first and last name from the name field.
     */
    protected function formatUserName($user): string
    {
        // Check if user has first_name and last_name attributes (might be accessors)
        if (isset($user->first_name) && isset($user->last_name)) {
            return trim($user->first_name . ' ' . $user->last_name);
        }

        // Try to parse the name field
        $name = trim($user->name ?? '');
        
        if (empty($name)) {
            return 'Unknown User';
        }

        // Split name by spaces
        $nameParts = preg_split('/\s+/', $name);
        
        if (count($nameParts) >= 2) {
            // Has at least first and last name
            $firstName = $nameParts[0];
            $lastName = end($nameParts);
            return trim($firstName . ' ' . $lastName);
        }

        // Fallback to full name if can't be split
        return $name;
    }
}

