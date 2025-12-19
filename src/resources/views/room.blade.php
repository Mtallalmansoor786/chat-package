@extends('layouts.app')

@section('content')
<div class="chat-container" style="height: calc(100vh - 100px); margin: 0; padding: 0;">
    <div class="row g-0 h-100">
        <!-- Left Sidebar: Chat List (WhatsApp Style) -->
        <div class="col-md-3 border-end chat-sidebar" style="height: 100%; overflow-y: auto;">
            <div class="d-flex flex-column h-100">
                <!-- Chat List Header -->
                <div class="chat-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="chat-header-icon me-2">
                                <i class="bi bi-chat-dots-fill"></i>
                            </div>
                            <h5 class="mb-0 fw-bold">Chats</h5>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary btn-create-room" data-bs-toggle="modal" data-bs-target="#createRoomModal">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Chat List -->
                <div class="flex-grow-1 overflow-auto">
                    @if($chatRooms->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($chatRooms as $room)
                                <a href="{{ route('chat.show', $room->id) }}" 
                                   class="list-group-item list-group-item-action border-0 px-3 py-3 chat-room-item {{ isset($chatRoom) && $room->id === $chatRoom->id ? 'active' : '' }}">
                                    <div class="d-flex align-items-center">
                                        <!-- Avatar -->
                                        <div class="chat-avatar me-3 position-relative">
                                            <div class="chat-avatar-circle">
                                                @php
                                                    $displayName = $room->getDisplayName(Auth::id());
                                                    $avatarText = strtoupper(substr($displayName, 0, 2));
                                                @endphp
                                                <span class="chat-avatar-text">{{ $avatarText }}</span>
                                            </div>
                                        </div>
                                        
                                        <!-- Chat Info -->
                                        <div class="flex-grow-1 chat-info">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <h6 class="mb-0 fw-semibold chat-room-name">
                                                    {{ $displayName }}
                                                </h6>
                                                <div class="d-flex align-items-center gap-2">
                                                    @if(isset($unreadCounts[$room->id]) && $unreadCounts[$room->id] > 0)
                                                        <span class="unread-badge">{{ $unreadCounts[$room->id] > 99 ? '99+' : $unreadCounts[$room->id] }}</span>
                                                    @endif
                                                    <small class="chat-time">
                                                        {{ $room->updated_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                            </div>
                                            
                                            @if($room->messages->count() > 0)
                                                <p class="mb-0 chat-preview">
                                                    {{ Str::limit($room->messages->first()->message, 40) }}
                                                </p>
                                            @else
                                                <p class="mb-0 chat-preview text-muted">
                                                    No messages yet
                                                </p>
                                            @endif
                                            
                                            <div class="d-flex align-items-center mt-1">
                                                <span class="chat-meta">
                                                    <i class="bi bi-people-fill me-1"></i>{{ $room->users->count() }} members
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="chat-empty-state">
                            <div class="chat-empty-icon">
                                <i class="bi bi-chat-dots"></i>
                            </div>
                            <p class="chat-empty-text">No chat rooms yet.</p>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createRoomModal">
                                <i class="bi bi-plus-circle me-1"></i>Create Room
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Center: Chat Messages Area -->
        <div class="col-md-5 d-flex flex-column" style="height: 100%;">
            <div class="card border-0 h-100 d-flex flex-column">
                @if(isset($chatRoom) && $chatRoom)
                    <!-- Chat Header -->
                    <div class="chat-messages-header">
                        <div class="d-flex align-items-center">
                            <div class="chat-header-avatar me-3">
                                @php
                                    $chatDisplayName = $chatRoom->getDisplayName(Auth::id());
                                    $chatAvatarText = strtoupper(substr($chatDisplayName, 0, 2));
                                @endphp
                                <span class="chat-header-avatar-text">{{ $chatAvatarText }}</span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fw-bold chat-room-title">{{ $chatDisplayName }}</h6>
                                @if($chatRoom->description && !$chatRoom->isPeerToPeer())
                                    <small class="text-muted chat-room-subtitle">{{ Str::limit($chatRoom->description, 50) }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Messages Container -->
                    <div class="chat-messages-body">
                        <div id="messagesContainer" class="messages-container">
                            @php
                                $firstUnreadFound = false;
                            @endphp
                            @foreach($messages->reverse() as $message)
                                @php
                                    $isUnread = $message->user_id !== Auth::id() && !$message->isReadBy(Auth::id());
                                    if ($isUnread && !$firstUnreadFound && isset($firstUnreadMessageId) && $message->id == $firstUnreadMessageId) {
                                        $firstUnreadFound = true;
                                    }
                                @endphp
                                <div class="message-wrapper {{ $message->user_id === Auth::id() ? 'message-own' : 'message-other' }} {{ $isUnread ? 'message-unread' : '' }}" 
                                     data-message-id="{{ $message->id }}"
                                     @if($firstUnreadFound && !isset($scrollDone)) id="first-unread-message" @php $scrollDone = true; @endphp @endif>
                                    @if($firstUnreadFound && !isset($unreadDividerShown))
                                        <div class="unread-divider">
                                            <span>Unread messages</span>
                                        </div>
                                        @php $unreadDividerShown = true; @endphp
                                    @endif
                                    <div class="message-bubble {{ $message->user_id === Auth::id() ? 'message-bubble-own' : 'message-bubble-other' }}">
                                        @if($message->user_id !== Auth::id())
                                            <div class="message-sender">
                                                {{ $message->user->name }}
                                            </div>
                                        @endif
                                        <div class="message-content">
                                            {{ $message->message }}
                                        </div>
                                        <div class="message-time">
                                            {{ $message->created_at->format('H:i') }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Message Input -->
                        <div class="chat-input-container">
                            <form id="messageForm" class="chat-input-form">
                                @csrf
                                <div class="chat-input-wrapper">
                                    <input type="text" id="messageInput" class="chat-input" 
                                           placeholder="Type a message..." required>
                                    <button type="submit" class="chat-send-btn">
                                        <i class="bi bi-send-fill"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- No Chat Selected -->
                    <div class="chat-welcome-screen">
                        <div class="chat-welcome-content">
                            <div class="chat-welcome-icon">
                                <i class="bi bi-chat-dots"></i>
                            </div>
                            <h3 class="chat-welcome-title">Please select a chat</h3>
                            <p class="chat-welcome-text">Choose a conversation from the left to start messaging</p>
                            @if($chatRooms->count() === 0)
                                <button type="button" class="btn btn-primary btn-lg mt-3" data-bs-toggle="modal" data-bs-target="#createRoomModal">
                                    <i class="bi bi-plus-circle me-2"></i>Create Your First Chat Room
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Sidebar: Users/Peers -->
        <div class="col-md-4 border-start bg-white" style="height: 100%; overflow-y: auto;">
            <div class="d-flex flex-column h-100">
                @if(isset($chatRoom) && $chatRoom)
                    <!-- Peers Header -->
                    <div class="members-header">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-people-fill me-2"></i>Members ({{ $chatRoom->users->count() }})
                        </h6>
                    </div>
                    
                    <!-- Peers List -->
                    <div class="members-list">
                        @foreach($chatRoom->users as $user)
                            <div class="member-item">
                                <div class="d-flex align-items-center">
                                    <div class="member-avatar">
                                        <span class="member-avatar-text">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                    </div>
                                    <div class="flex-grow-1 member-info">
                                        <div class="member-name">{{ $user->name }}</div>
                                        <div class="member-email">{{ $user->email }}</div>
                                    </div>
                                    <div class="member-status">
                                        <span class="status-badge status-online" id="status-{{ $user->id }}">
                                            <span class="status-dot"></span>Online
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- No Chat Selected Message -->
                    <div class="members-empty">
                        <div class="members-empty-icon">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <p class="members-empty-text">Select a chat to view members</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Create Room Modal -->
<div class="modal fade" id="createRoomModal" tabindex="-1" aria-labelledby="createRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createRoomModalLabel">Create New Chat Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('chat.room.create') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="room_name" class="form-label">Room Name</label>
                        <input type="text" class="form-control" id="room_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="room_description" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="room_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="user_ids" class="form-label">Add Users</label>
                        @php
                            $userModel = config('auth.providers.users.model', \App\Models\User::class);
                            $users = $userModel::where('id', '!=', Auth::id())->get();
                        @endphp
                        @if($users->count() > 0)
                            <select class="form-select" id="user_ids" name="user_ids[]" multiple required>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple users</small>
                        @else
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>No other users available to add to the room.
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Room</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Pusher and Chat Scripts -->
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    // Track sent message IDs to avoid duplicates
    const sentMessageIds = new Set();
    const currentUserId = {{ Auth::id() }};
    
    @if(isset($chatRoom) && $chatRoom)
    // Initialize Pusher with authentication
    const pusher = new Pusher('{{ config('chat-package.pusher.key') }}', {
        cluster: '{{ config('chat-package.pusher.cluster') }}',
        encrypted: true,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }
    });

    // Subscribe to presence channel
    const channel = pusher.subscribe('presence-chat-room.{{ $chatRoom->id }}');
    
    // Handle subscription events
    channel.bind('pusher:subscription_succeeded', function(members) {
        console.log('✅ Successfully subscribed to channel');
    });
    
    channel.bind('pusher:subscription_error', function(status) {
        console.error('❌ Subscription error:', status);
        alert('Failed to connect to chat. Please refresh the page.');
    });

    // Function to add message to container
    function addMessageToContainer(data, isOwnMessage = false) {
        // Prevent duplicate messages
        if (sentMessageIds.has(data.id)) {
            return;
        }
        sentMessageIds.add(data.id);
        
        const messagesContainer = document.getElementById('messagesContainer');
        if (!messagesContainer) return;
        
        const messageHtml = `
            <div class="message-wrapper ${isOwnMessage ? 'message-own' : 'message-other'}" data-message-id="${data.id}">
                <div class="message-bubble ${isOwnMessage ? 'message-bubble-own' : 'message-bubble-other'}">
                    ${!isOwnMessage ? `<div class="message-sender">${escapeHtml(data.user.name)}</div>` : ''}
                    <div class="message-content">${escapeHtml(data.message)}</div>
                    <div class="message-time">${formatTime(data.created_at)}</div>
                </div>
            </div>
        `;
        
        messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Helper function to format time
    function formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('en-US', {hour: '2-digit', minute:'2-digit'});
    }

    // Handle new messages from broadcast
    channel.bind('message.sent', function(data) {
        console.log('📨 Message received:', data);
        const isOwnMessage = data.user_id === currentUserId;
        addMessageToContainer(data, isOwnMessage);
    });

    // Handle member added/removed
    channel.bind('pusher:member_added', function(member) {
        console.log('👤 Member added:', member);
        updatePeerStatus(member.id, 'online');
    });

    channel.bind('pusher:member_removed', function(member) {
        console.log('👤 Member removed:', member);
        updatePeerStatus(member.id, 'offline');
    });

    function updatePeerStatus(userId, status) {
        const statusBadge = document.getElementById('status-' + userId);
        if (statusBadge) {
            if (status === 'online') {
                statusBadge.className = 'status-badge status-online';
                statusBadge.innerHTML = '<span class="status-dot"></span>Online';
            } else {
                statusBadge.className = 'status-badge status-offline';
                statusBadge.innerHTML = '<span class="status-dot"></span>Offline';
            }
        }
    }

    // Send message
    const messageForm = document.getElementById('messageForm');
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const messageInput = document.getElementById('messageInput');
            const submitButton = this.querySelector('button[type="submit"]');
            const message = messageInput.value.trim();
            
            if (!message) return;
            
            // Disable form during submission
            messageInput.disabled = true;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="bi bi-hourglass-split"></i>';

            fetch('/api/chat/rooms/{{ $chatRoom->id }}/messages', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ message: message })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ Message sent successfully:', data);
            
            if (data.success && data.message) {
                // Add message immediately (optimistic update)
                const messageData = {
                    id: data.message.id,
                    user_id: data.message.user_id,
                    message: data.message.message,
                    created_at: data.message.created_at,
                    user: {
                        id: data.message.user?.id || currentUserId,
                        name: data.message.user?.name || '{{ Auth::user()->name }}',
                        email: data.message.user?.email || '{{ Auth::user()->email }}'
                    }
                };
                
                // Add message to container immediately
                addMessageToContainer(messageData, true);
                
                // Clear input
                messageInput.value = '';
            }
        })
        .catch(error => {
            console.error('❌ Error sending message:', error);
            alert(error.message || 'Failed to send message. Please try again.');
        })
        .finally(() => {
            // Re-enable form
            messageInput.disabled = false;
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="bi bi-send"></i>';
            messageInput.focus();
        });
        });
    }
    @endif

    // Auto-scroll to first unread message or bottom on load
    window.addEventListener('load', function() {
        const messagesContainer = document.getElementById('messagesContainer');
        if (messagesContainer) {
            // Try to scroll to first unread message
            const firstUnread = document.getElementById('first-unread-message');
            if (firstUnread) {
                firstUnread.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                // If no unread messages, scroll to bottom
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }
    });
</script>

<style>
    /* Main Container */
    .chat-container {
        background: #f5f5f5;
    }
    
    /* Left Sidebar */
    .chat-sidebar {
        background: #ffffff;
        box-shadow: 2px 0 10px rgba(0,0,0,0.05);
    }
    
    .chat-header {
        background: #667eea;
        padding: 1.25rem;
        color: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .chat-header-icon {
        font-size: 1.5rem;
        opacity: 0.95;
    }
    
    .btn-create-room {
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        color: white;
        transition: all 0.3s ease;
    }
    
    .btn-create-room:hover {
        background: rgba(255,255,255,0.3);
        transform: scale(1.05);
    }
    
    /* Chat Room Items */
    .chat-room-item {
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
        position: relative;
    }
    
    .chat-room-item:hover {
        background: #f3f4f6 !important;
        border-left-color: #667eea;
    }
    
    .chat-room-item.active {
        background: #eef2ff !important;
        border-left-color: #667eea;
    }
    
    .chat-avatar-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #667eea;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    }
    
    .chat-unread-indicator {
        position: absolute;
        top: 0;
        right: 0;
        width: 12px;
        height: 12px;
        background: #10b981;
        border: 2px solid white;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .chat-room-name {
        font-size: 0.95rem;
        color: #1f2937;
        font-weight: 600;
    }
    
    .chat-time {
        font-size: 0.75rem;
        color: #6b7280;
        white-space: nowrap;
    }
    
    .unread-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        background: #ef4444;
        color: white;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 600;
        line-height: 1;
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
    }
    
    .chat-preview {
        font-size: 0.85rem;
        color: #6b7280;
        line-height: 1.4;
    }
    
    .chat-meta {
        font-size: 0.75rem;
        color: #9ca3af;
    }
    
    .chat-empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
    }
    
    .chat-empty-icon {
        font-size: 4rem;
        color: #d1d5db;
        margin-bottom: 1rem;
    }
    
    .chat-empty-text {
        color: #6b7280;
        margin-bottom: 1.5rem;
    }
    
    /* Messages Area */
    .chat-messages-header {
        background: #667eea;
        padding: 1.25rem;
        color: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .chat-header-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.85rem;
    }
    
    .chat-room-title {
        color: white;
        font-size: 1.1rem;
    }
    
    .chat-room-subtitle {
        color: rgba(255,255,255,0.8);
        font-size: 0.85rem;
    }
    
    .chat-messages-body {
        background: #f3f4f6;
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        position: relative;
    }
    
    .messages-container {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        background: #f3f4f6;
    }
    
    .message-wrapper {
        margin-bottom: 0.75rem;
        animation: messageSlideIn 0.3s ease-out;
    }
    
    .message-own {
        text-align: right;
    }
    
    .message-other {
        text-align: left;
    }
    
    .message-bubble {
        display: inline-block;
        max-width: 75%;
        padding: 0.75rem 1rem;
        border-radius: 1.25rem;
        word-wrap: break-word;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        position: relative;
    }
    
    .message-bubble-own {
        background: #667eea;
        color: white;
        border-bottom-right-radius: 0.25rem;
    }
    
    .message-bubble-other {
        background: white;
        color: #1f2937;
        border-bottom-left-radius: 0.25rem;
    }
    
    .message-sender {
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
        opacity: 0.9;
        color: #667eea;
    }
    
    .message-content {
        font-size: 0.95rem;
        line-height: 1.5;
        margin-bottom: 0.25rem;
    }
    
    .message-time {
        font-size: 0.7rem;
        text-align: right;
        opacity: 0.7;
        margin-top: 0.25rem;
    }
    
    .message-unread {
        opacity: 0.85;
    }
    
    .message-unread .message-bubble {
        border-left: 3px solid #ef4444;
    }
    
    .unread-divider {
        text-align: center;
        margin: 1rem 0;
        position: relative;
    }
    
    .unread-divider::before,
    .unread-divider::after {
        content: '';
        position: absolute;
        top: 50%;
        width: 40%;
        height: 1px;
        background: #e5e7eb;
    }
    
    .unread-divider::before {
        left: 0;
    }
    
    .unread-divider::after {
        right: 0;
    }
    
    .unread-divider span {
        background: #f3f4f6;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        color: #ef4444;
        font-weight: 600;
        position: relative;
        z-index: 1;
    }
    
    .chat-input-container {
        background: white;
        padding: 1rem 1.25rem;
        border-top: 1px solid #e5e7eb;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
    }
    
    .chat-input-wrapper {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }
    
    .chat-input {
        flex: 1;
        border: 2px solid #e5e7eb;
        border-radius: 2rem;
        padding: 0.75rem 1.25rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }
    
    .chat-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .chat-send-btn {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: #667eea;
        border: none;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    
    .chat-send-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.5);
    }
    
    .chat-send-btn:active {
        transform: scale(0.95);
    }
    
    /* Welcome Screen */
    .chat-welcome-screen {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f3f4f6;
    }
    
    .chat-welcome-content {
        text-align: center;
        padding: 2rem;
    }
    
    .chat-welcome-icon {
        font-size: 6rem;
        color: #d1d5db;
        margin-bottom: 1.5rem;
        opacity: 0.6;
    }
    
    .chat-welcome-title {
        color: #374151;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }
    
    .chat-welcome-text {
        color: #6b7280;
        margin-bottom: 1.5rem;
    }
    
    /* Members Sidebar */
    .members-header {
        background: white;
        padding: 1.25rem;
        border-bottom: 1px solid #e5e7eb;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .members-list {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
    }
    
    .member-item {
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 0.5rem;
        transition: all 0.2s ease;
    }
    
    .member-item:hover {
        background: #f9fafb;
    }
    
    .member-avatar {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: #667eea;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.85rem;
        box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);
        margin-right: 1rem;
        flex-shrink: 0;
    }
    
    .member-info {
        min-width: 0;
        padding-right: 0.75rem;
    }
    
    .member-name {
        font-weight: 600;
        color: #1f2937;
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
        line-height: 1.3;
    }
    
    .member-email {
        color: #6b7280;
        font-size: 0.8rem;
        line-height: 1.3;
        word-break: break-word;
    }
    
    .member-status {
        flex-shrink: 0;
        margin-left: auto;
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .status-online {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-offline {
        background: #e5e7eb;
        color: #6b7280;
    }
    
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
    }
    
    .members-empty {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }
    
    .members-empty-icon {
        font-size: 3rem;
        color: #d1d5db;
        opacity: 0.5;
        margin-bottom: 1rem;
    }
    
    .members-empty-text {
        color: #9ca3af;
        font-size: 0.9rem;
    }
    
    /* Animations */
    @keyframes messageSlideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Scrollbars */
    .messages-container::-webkit-scrollbar,
    .chat-sidebar::-webkit-scrollbar,
    .members-list::-webkit-scrollbar {
        width: 6px;
    }
    
    .messages-container::-webkit-scrollbar-track,
    .chat-sidebar::-webkit-scrollbar-track,
    .members-list::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .messages-container::-webkit-scrollbar-thumb,
    .chat-sidebar::-webkit-scrollbar-thumb,
    .members-list::-webkit-scrollbar-thumb {
        background: rgba(0,0,0,0.2);
        border-radius: 3px;
    }
    
    .messages-container::-webkit-scrollbar-thumb:hover,
    .chat-sidebar::-webkit-scrollbar-thumb:hover,
    .members-list::-webkit-scrollbar-thumb:hover {
        background: rgba(0,0,0,0.3);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .col-md-3, .col-md-4 {
            display: none !important;
        }
        .col-md-5 {
            width: 100% !important;
        }
    }
</style>
@endsection
