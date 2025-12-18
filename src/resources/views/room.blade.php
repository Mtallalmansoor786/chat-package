@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row g-3">
        <!-- Chat Messages Area -->
        <div class="col-md-9">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $chatRoom->name }}</h5>
                            @if($chatRoom->description)
                                <small class="text-muted">{{ $chatRoom->description }}</small>
                            @endif
                        </div>
                        <a href="{{ route('chat.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
                <div class="card-body d-flex flex-column" style="height: calc(100vh - 250px);">
                    <!-- Messages Container -->
                    <div id="messagesContainer" class="flex-grow-1 overflow-auto mb-3" style="max-height: 100%;">
                        @foreach($messages->reverse() as $message)
                            <div class="message-item mb-3 {{ $message->user_id === Auth::id() ? 'text-end' : '' }}">
                                <div class="d-inline-block {{ $message->user_id === Auth::id() ? 'bg-primary text-white' : 'bg-light' }} rounded p-2" style="max-width: 70%;">
                                    @if($message->user_id !== Auth::id())
                                        <div class="fw-bold small mb-1">{{ $message->user->name }}</div>
                                    @endif
                                    <div class="message-text">{{ $message->message }}</div>
                                    <div class="small {{ $message->user_id === Auth::id() ? 'text-white-50' : 'text-muted' }} mt-1">
                                        {{ $message->created_at->format('H:i') }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Message Input -->
                    <div class="border-top pt-3">
                        <form id="messageForm" class="d-flex gap-2">
                            @csrf
                            <input type="text" id="messageInput" class="form-control" placeholder="Type your message..." required>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Send
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Peers Sidebar -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-bold">
                        <i class="bi bi-people me-2"></i>Peers ({{ $chatRoom->users->count() }})
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th scope="col" class="border-0">User</th>
                                    <th scope="col" class="border-0">Status</th>
                                </tr>
                            </thead>
                            <tbody id="peersTable">
                                @foreach($chatRoom->users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-person text-primary"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-medium small">{{ $user->name }}</div>
                                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $user->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-success" id="status-{{ $user->id }}">Online</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pusher and Chat Scripts -->
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    // Track sent message IDs to avoid duplicates
    const sentMessageIds = new Set();
    const currentUserId = {{ Auth::id() }};
    
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
            <div class="message-item mb-3 ${isOwnMessage ? 'text-end' : ''}" data-message-id="${data.id}">
                <div class="d-inline-block ${isOwnMessage ? 'bg-primary text-white' : 'bg-light'} rounded p-2" style="max-width: 70%;">
                    ${!isOwnMessage ? `<div class="fw-bold small mb-1">${escapeHtml(data.user.name)}</div>` : ''}
                    <div class="message-text">${escapeHtml(data.message)}</div>
                    <div class="small ${isOwnMessage ? 'text-white-50' : 'text-muted'} mt-1">
                        ${formatTime(data.created_at)}
                    </div>
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
            statusBadge.textContent = status === 'online' ? 'Online' : 'Offline';
            statusBadge.className = 'badge bg-' + (status === 'online' ? 'success' : 'secondary');
        }
    }

    // Send message
    document.getElementById('messageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const messageInput = document.getElementById('messageInput');
        const submitButton = this.querySelector('button[type="submit"]');
        const message = messageInput.value.trim();
        
        if (!message) return;
        
        // Disable form during submission
        messageInput.disabled = true;
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Sending...';

        fetch('{{ route('chat.message.send', $chatRoom->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
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
            submitButton.innerHTML = '<i class="bi bi-send"></i> Send';
            messageInput.focus();
        });
    });

    // Auto-scroll to bottom on load
    window.addEventListener('load', function() {
        const messagesContainer = document.getElementById('messagesContainer');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    });
</script>

<style>
    .message-item {
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    #messagesContainer::-webkit-scrollbar {
        width: 6px;
    }
    
    #messagesContainer::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    #messagesContainer::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }
    
    #messagesContainer::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
@endsection

