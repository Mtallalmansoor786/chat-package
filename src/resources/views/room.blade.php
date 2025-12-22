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
                <div class="flex-grow-1 overflow-auto" id="chatRoomsSidebar">
                    <div class="text-center py-5" id="sidebarLoader">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading chats...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Center: Chat Messages Area -->
        <div class="col-md-5 d-flex flex-column chat-area" id="chatArea" style="height: 100%;">
            <div class="card border-0 h-100 d-flex flex-column">
                <!-- Chat Header (loaded via JS) -->
                <div id="chatHeader" style="display: none;">
                    <div class="chat-messages-header">
                        <div class="d-flex align-items-center">
                            <div class="chat-header-avatar me-3">
                                <span class="chat-header-avatar-text" id="chatHeaderAvatar">--</span>
                            </div>
                            <div class="flex-grow-1 chat-title-clickable" id="chatTitleContainer" style="cursor: pointer;" title="Click to view details">
                                <h6 class="mb-0 fw-bold chat-room-title" id="chatRoomTitle">Loading...</h6>
                                <small class="text-muted chat-room-subtitle" id="chatRoomSubtitle"></small>
                            </div>
                            <div class="chat-title-info-icon ms-2" style="opacity: 0.7;">
                                <i class="bi bi-info-circle" style="font-size: 1.1rem;"></i>
                            </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Messages Container -->
                <div class="chat-messages-body" id="chatMessagesBody" style="display: none;">
                        <div id="messagesContainer" class="messages-container">
                        <!-- Messages will be loaded here via JavaScript -->
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
                
                <!-- No Chat Selected / Loading -->
                <div id="chatWelcomeScreen" class="chat-welcome-screen">
                        <div class="chat-welcome-content">
                            <div class="chat-welcome-icon">
                                <i class="bi bi-chat-dots"></i>
                            </div>
                        <h3 class="chat-welcome-title" id="welcomeTitle">Loading chat...</h3>
                        <p class="chat-welcome-text" id="welcomeText">Please wait while we load the chat room.</p>
                        </div>
                    </div>
            </div>
        </div>

        <!-- Sidebar Backdrop (for mobile) -->
        <div class="sidebar-backdrop sidebar-backdrop-hidden" id="sidebarBackdrop"></div>
        
        <!-- Right Sidebar: Chat Details / User Details -->
        <div class="col-md-4 border-start bg-white right-sidebar sidebar-hidden" id="rightSidebar" style="height: 100%; overflow-y: auto;">
            <div class="d-flex flex-column h-100">
                <!-- Sidebar Header with Close Button -->
                <div class="sidebar-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold" id="sidebarTitle">Details</h6>
                        <button type="button" class="btn btn-sm btn-link text-muted p-0" id="closeSidebarBtn" style="font-size: 1.5rem; line-height: 1;">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Sidebar Content Container -->
                <div class="flex-grow-1 overflow-auto" id="sidebarContent">
                    <!-- Content will be loaded here via JavaScript -->
                </div>
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
            <form id="createRoomFormInRoom">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="room_name_modal" class="form-label">Room Name</label>
                        <input type="text" class="form-control" id="room_name_modal" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="room_description_modal" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="room_description_modal" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="user_ids_modal" class="form-label">Add Users</label>
                        <select class="form-select" id="user_ids_modal" name="user_ids[]" multiple required>
                            <option value="">Loading users...</option>
                            </select>
                            <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple users</small>
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
    const apiBaseUrl = '/api/chat';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Get roomId from URL or passed variable
    const urlParts = window.location.pathname.split('/');
    const roomId = {{ $roomId ?? 'null' }};
    let currentRoomId = roomId;
    let pusher = null;
    let channel = null;
    let currentRoomData = null; // Store current room data
    let currentPeers = []; // Store current peers/members
    
    // Load chat rooms for sidebar
    function loadChatRoomsSidebar(showLoader = false) {
        const container = document.getElementById('chatRoomsSidebar');
        const loader = document.getElementById('sidebarLoader');
        
        // Only show loader on initial load
        if (showLoader && loader) {
            loader.style.display = 'block';
        }
        
        fetch(`${apiBaseUrl}/rooms`, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            const rooms = data.chat_rooms || data.rooms || [];
            
            if (rooms.length > 0) {
                container.innerHTML = '<div class="list-group list-group-flush" id="chatRoomsList">' + rooms.map(room => {
                    const isActive = room.id == currentRoomId ? 'active' : '';
                    const displayName = room.display_name || room.name || 'Chat Room';
                    const avatarText = displayName.substring(0, 2).toUpperCase();
                    const lastMessage = room.last_message ? room.last_message.message.substring(0, 40) : 'No messages yet';
                    
                    return `
                        <a href="/chat/room/${room.id}" class="list-group-item list-group-item-action border-0 px-3 py-3 chat-room-item ${isActive}" data-room-id="${room.id}">
                            <div class="d-flex align-items-center">
                                <div class="chat-avatar me-3 position-relative">
                                    <div class="chat-avatar-circle">
                                        <span class="chat-avatar-text">${avatarText}</span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 chat-info">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h6 class="mb-0 fw-semibold chat-room-name">${displayName}</h6>
                                        <div class="d-flex align-items-center gap-2">
                                            ${room.unread_count > 0 ? `<span class="unread-badge">${room.unread_count > 99 ? '99+' : room.unread_count}</span>` : ''}
                                            <small class="chat-time">${new Date(room.updated_at).toLocaleDateString()}</small>
                                        </div>
                                    </div>
                                    <p class="mb-0 chat-preview ${!room.last_message ? 'text-muted' : ''}">${lastMessage}</p>
                                    <div class="d-flex align-items-center mt-1">
                                        <span class="chat-meta">
                                            <i class="bi bi-people-fill me-1"></i>${room.members_count || 0} members
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    `;
                }).join('') + '</div>';
                
                // Attach click handlers to prevent reload
                attachChatRoomClickHandlers();
            } else {
                container.innerHTML = `
                    <div class="chat-empty-state">
                        <div class="chat-empty-icon">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                        <p class="chat-empty-text">No chat rooms yet.</p>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createRoomModal">
                            <i class="bi bi-plus-circle me-1"></i>Create Room
                        </button>
                    </div>
                `;
            }
            
            // Hide loader after content is loaded
            if (loader) {
                loader.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error loading chat rooms:', error);
            container.innerHTML = `
                <div class="alert alert-danger m-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>Failed to load chat rooms.
                </div>
            `;
            if (loader) {
                loader.style.display = 'none';
            }
        });
    }
    
    // Attach click handlers to chat room links to prevent page reload
    function attachChatRoomClickHandlers() {
        const chatRoomLinks = document.querySelectorAll('.chat-room-item[data-room-id]');
        chatRoomLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const roomId = this.getAttribute('data-room-id');
                if (roomId && roomId != currentRoomId) {
                    switchToChat(roomId);
                }
            });
        });
    }
    
    // Switch to a different chat without page reload
    function switchToChat(roomId) {
        if (!roomId || roomId == currentRoomId) return;
        
        // Close right sidebar if it's open when switching chats
        const sidebar = document.getElementById('rightSidebar');
        if (sidebar && sidebar.classList.contains('sidebar-visible')) {
            closeRightSidebar();
        }
        
        // Update URL without reload
        window.history.pushState({ roomId: roomId }, '', `/chat/room/${roomId}`);
        
        // Update active state in sidebar immediately (optimistic update)
        updateSidebarActiveState(roomId);
        
        // Load the new chat room
        loadChatRoom(roomId);
    }
    
    // Update active state in sidebar without reloading
    function updateSidebarActiveState(roomId) {
        const chatRoomItems = document.querySelectorAll('.chat-room-item');
        chatRoomItems.forEach(item => {
            const itemRoomId = item.getAttribute('data-room-id');
            if (itemRoomId == roomId) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }
    
    // Load specific chat room data and messages
    function loadChatRoom(roomId) {
        if (!roomId) {
            document.getElementById('welcomeTitle').textContent = 'Please select a chat';
            document.getElementById('welcomeText').textContent = 'Choose a conversation from the left to start messaging';
            return;
        }
        
        currentRoomId = roomId;
        
        // Show loading state with smooth transition
        const welcomeScreen = document.getElementById('chatWelcomeScreen');
        const chatBody = document.getElementById('chatMessagesBody');
        const chatHeader = document.getElementById('chatHeader');
        
        // Smooth transition to loading state
        if (chatBody.style.display !== 'none') {
            chatBody.style.opacity = '0.5';
        }
        welcomeScreen.style.display = 'flex';
        chatBody.style.display = 'none';
        chatHeader.style.display = 'none';
        document.getElementById('welcomeTitle').textContent = 'Loading chat...';
        document.getElementById('welcomeText').textContent = 'Please wait while we load the chat room.';
        
        fetch(`${apiBaseUrl}/rooms/${roomId}`, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to load chat room');
            }
            return response.json();
        })
        .then(data => {
            console.log('Chat room data:', data);
            
            if (data.success && data.chat_room) {
                const room = data.chat_room;
                const displayName = room.display_name || room.name || 'Chat Room';
                const avatarText = displayName.substring(0, 2).toUpperCase();
                
                // Store room data and peers
                currentRoomData = room;
                currentPeers = data.peers || [];
                
                // Update header
                document.getElementById('chatHeaderAvatar').textContent = avatarText;
                document.getElementById('chatRoomTitle').textContent = displayName;
                document.getElementById('chatRoomSubtitle').textContent = room.description || '';
                
                // Load messages
                loadMessages(roomId, data.messages || []);
                
                // Initialize Pusher
                initializePusher(roomId);
                
                // Show chat area with smooth transition
                chatHeader.style.display = 'block';
                chatBody.style.display = 'flex';
                chatBody.style.opacity = '1';
                welcomeScreen.style.display = 'none';
                
                // Update sidebar active state without reloading
                updateSidebarActiveState(roomId);
            } else {
                throw new Error(data.error || 'Failed to load chat room');
            }
        })
        .catch(error => {
            console.error('Error loading chat room:', error);
            welcomeScreen.style.display = 'flex';
            chatBody.style.display = 'none';
            chatHeader.style.display = 'none';
            document.getElementById('welcomeTitle').textContent = 'Error loading chat';
            document.getElementById('welcomeText').textContent = error.message || 'Failed to load chat room. Please try again.';
        });
    }
    
    // Load messages into container
    function loadMessages(roomId, messages) {
        const container = document.getElementById('messagesContainer');
        
        if (!messages || messages.length === 0) {
            container.innerHTML = '<div class="text-center py-5 text-muted">No messages yet. Start the conversation!</div>';
            return;
        }
        
        container.innerHTML = messages.map(msg => {
            const isOwn = msg.user_id == currentUserId;
            const messageTime = new Date(msg.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            
            return `
                <div class="message-wrapper ${isOwn ? 'message-own' : 'message-other'}" data-message-id="${msg.id}">
                    <div class="message-bubble ${isOwn ? 'message-bubble-own' : 'message-bubble-other'}">
                        ${!isOwn ? `<div class="message-sender">${msg.user?.name || 'Unknown'}</div>` : ''}
                        <div class="message-content">${msg.message}</div>
                        <div class="message-time">${messageTime}</div>
                    </div>
                </div>
            `;
        }).join('');
        
        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    }
    
    // Toggle right sidebar
    function toggleRightSidebar() {
        const sidebar = document.getElementById('rightSidebar');
        const chatArea = document.getElementById('chatArea');
        const backdrop = document.getElementById('sidebarBackdrop');
        
        if (sidebar.classList.contains('sidebar-hidden')) {
            // Show sidebar
            showSidebarContent();
            sidebar.classList.remove('sidebar-hidden');
            sidebar.classList.add('sidebar-visible');
            if (backdrop) {
                backdrop.classList.remove('sidebar-backdrop-hidden');
                backdrop.classList.add('sidebar-backdrop-visible');
            }
            chatArea.classList.remove('chat-area-full');
            chatArea.classList.add('chat-area-with-sidebar');
        } else {
            // Hide sidebar
            sidebar.classList.remove('sidebar-visible');
            sidebar.classList.add('sidebar-hidden');
            if (backdrop) {
                backdrop.classList.remove('sidebar-backdrop-visible');
                backdrop.classList.add('sidebar-backdrop-hidden');
            }
            chatArea.classList.remove('chat-area-with-sidebar');
            chatArea.classList.add('chat-area-full');
        }
    }
    
    // Close sidebar and expand chat area
    function closeRightSidebar() {
        const sidebar = document.getElementById('rightSidebar');
        const chatArea = document.getElementById('chatArea');
        const backdrop = document.getElementById('sidebarBackdrop');
        sidebar.classList.remove('sidebar-visible');
        sidebar.classList.add('sidebar-hidden');
        if (backdrop) {
            backdrop.classList.remove('sidebar-backdrop-visible');
            backdrop.classList.add('sidebar-backdrop-hidden');
        }
        chatArea.classList.remove('chat-area-with-sidebar');
        chatArea.classList.add('chat-area-full');
    }
    
    // Show sidebar content based on chat type
    function showSidebarContent() {
        if (!currentRoomData) return;
        
        const sidebar = document.getElementById('rightSidebar');
        const sidebarContent = document.getElementById('sidebarContent');
        const sidebarTitle = document.getElementById('sidebarTitle');
        
        if (currentRoomData.is_peer_to_peer) {
            // Show peer-to-peer user details
            showPeerDetails(sidebarContent, sidebarTitle);
        } else {
            // Show group details with members list
            showGroupDetails(sidebarContent, sidebarTitle);
        }
    }
    
    // Show peer-to-peer user details
    function showPeerDetails(container, titleElement) {
        // Find the other peer (not current user)
        const otherPeer = currentPeers.find(peer => peer.id != currentUserId);
        
        if (!otherPeer) {
            container.innerHTML = '<div class="p-4 text-center text-muted">No peer found</div>';
            return;
        }
        
        titleElement.textContent = 'Contact Info';
        const avatarText = (otherPeer.name || otherPeer.email || 'U').substring(0, 2).toUpperCase();
        
        container.innerHTML = `
            <div class="sidebar-content-wrapper">
                <div class="user-details-header">
                    <div class="user-details-avatar-large">
                        <span class="user-details-avatar-text-large">${avatarText}</span>
                    </div>
                    <h5 class="user-details-name">${escapeHtml(otherPeer.name || 'Unknown')}</h5>
                    <p class="user-details-email">${escapeHtml(otherPeer.email || '')}</p>
                </div>
                <div class="user-details-body">
                    <div class="user-details-section">
                        <h6 class="user-details-section-title">Contact Information</h6>
                        <div class="user-details-item">
                            <div class="user-details-item-label">
                                <i class="bi bi-envelope me-2"></i>Email
                            </div>
                            <div class="user-details-item-value">${escapeHtml(otherPeer.email || 'Not available')}</div>
                        </div>
                        <div class="user-details-item">
                            <div class="user-details-item-label">
                                <i class="bi bi-person me-2"></i>Name
                            </div>
                            <div class="user-details-item-value">${escapeHtml(otherPeer.name || 'Unknown')}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Show group details with members list
    function showGroupDetails(container, titleElement) {
        titleElement.textContent = 'Group Info';
        const avatarText = (currentRoomData.display_name || currentRoomData.name || 'G').substring(0, 2).toUpperCase();
        
        container.innerHTML = `
            <div class="sidebar-content-wrapper">
                <div class="group-details-header">
                    <div class="group-details-avatar-large">
                        <span class="group-details-avatar-text-large">${avatarText}</span>
                    </div>
                    <h5 class="group-details-name">${escapeHtml(currentRoomData.display_name || currentRoomData.name || 'Group')}</h5>
                    ${currentRoomData.description ? `<p class="group-details-description">${escapeHtml(currentRoomData.description)}</p>` : ''}
                    <div class="group-details-meta">
                        <span class="group-details-meta-item">
                            <i class="bi bi-people-fill me-1"></i>${currentPeers.length} members
                        </span>
                        <span class="group-details-meta-item">
                            <i class="bi bi-calendar me-1"></i>Created ${formatDate(currentRoomData.created_at)}
                        </span>
                    </div>
                </div>
                <div class="group-details-body">
                    <div class="group-details-section">
                        <h6 class="group-details-section-title">
                            <i class="bi bi-people-fill me-2"></i>Members (${currentPeers.length})
                        </h6>
                        <div class="members-list-group">
                            ${currentPeers.map(peer => {
                                const peerAvatarText = (peer.name || peer.email || 'U').substring(0, 2).toUpperCase();
                                const isCurrentUser = peer.id == currentUserId;
                                return `
                                    <div class="member-item-clickable" data-peer-id="${peer.id}" style="cursor: pointer;">
                                        <div class="d-flex align-items-center">
                                            <div class="member-avatar">
                                                <span class="member-avatar-text">${peerAvatarText}</span>
                                            </div>
                                            <div class="flex-grow-1 member-info">
                                                <div class="member-name">
                                                    ${escapeHtml(peer.name || 'Unknown')} ${isCurrentUser ? '<span class="text-muted">(You)</span>' : ''}
                                                </div>
                                                <div class="member-email">${escapeHtml(peer.email || '')}</div>
                                            </div>
                                            <div class="member-arrow">
                                                <i class="bi bi-chevron-right"></i>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Attach click handlers to members
        attachMemberClickHandlers();
    }
    
    // Show individual member details
    function showMemberDetails(peerId) {
        const peer = currentPeers.find(p => p.id == peerId);
        if (!peer) return;
        
        const sidebarContent = document.getElementById('sidebarContent');
        const sidebarTitle = document.getElementById('sidebarTitle');
        
        sidebarTitle.innerHTML = `
            <button type="button" class="btn btn-sm btn-link text-muted p-0 me-2" id="backToGroupBtn" style="font-size: 1.2rem;">
                <i class="bi bi-arrow-left"></i>
            </button>
            Contact Info
        `;
        
        const avatarText = (peer.name || peer.email || 'U').substring(0, 2).toUpperCase();
        
        sidebarContent.innerHTML = `
            <div class="sidebar-content-wrapper">
                <div class="user-details-header">
                    <div class="user-details-avatar-large">
                        <span class="user-details-avatar-text-large">${avatarText}</span>
                    </div>
                    <h5 class="user-details-name">${escapeHtml(peer.name || 'Unknown')}</h5>
                    <p class="user-details-email">${escapeHtml(peer.email || '')}</p>
                </div>
                <div class="user-details-body">
                    <div class="user-details-section">
                        <h6 class="user-details-section-title">Contact Information</h6>
                        <div class="user-details-item">
                            <div class="user-details-item-label">
                                <i class="bi bi-envelope me-2"></i>Email
                            </div>
                            <div class="user-details-item-value">${escapeHtml(peer.email || 'Not available')}</div>
                        </div>
                        <div class="user-details-item">
                            <div class="user-details-item-label">
                                <i class="bi bi-person me-2"></i>Name
                            </div>
                            <div class="user-details-item-value">${escapeHtml(peer.name || 'Unknown')}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Attach back button handler
        document.getElementById('backToGroupBtn').addEventListener('click', function() {
            showGroupDetails(sidebarContent, sidebarTitle);
        });
    }
    
    // Attach click handlers to members
    function attachMemberClickHandlers() {
        const memberItems = document.querySelectorAll('.member-item-clickable');
        memberItems.forEach(item => {
            item.addEventListener('click', function() {
                const peerId = parseInt(this.getAttribute('data-peer-id'));
                if (peerId) {
                    showMemberDetails(peerId);
                }
            });
        });
    }
    
    // Helper function to format date
    function formatDate(dateString) {
        if (!dateString) return 'Unknown';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }
    
    // Initialize Pusher for real-time messaging
    function initializePusher(roomId) {
        if (pusher) {
            pusher.disconnect();
        }
        
        if (!roomId) return;
        
    // Initialize Pusher with authentication
        pusher = new Pusher('{{ config('chat-package.pusher.key') }}', {
        cluster: '{{ config('chat-package.pusher.cluster') }}',
        encrypted: true,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                    'X-CSRF-TOKEN': csrfToken
            }
        }
    });

    // Subscribe to presence channel
        channel = pusher.subscribe('presence-chat-room.' + roomId);
    
    // Handle subscription events
    channel.bind('pusher:subscription_succeeded', function(members) {
        console.log('✅ Successfully subscribed to channel');
    });
    
    channel.bind('pusher:subscription_error', function(status) {
        console.error('❌ Subscription error:', status);
        alert('Failed to connect to chat. Please refresh the page.');
    });

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
    }

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
                    ${!isOwnMessage ? `<div class="message-sender">${escapeHtml(data.user?.name || 'Unknown')}</div>` : ''}
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

            if (!currentRoomId) {
                alert('Please select a chat room first');
                return;
            }
            
            fetch(`${apiBaseUrl}/rooms/${currentRoomId}/messages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
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
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(event) {
        const roomId = event.state ? event.state.roomId : null;
        if (roomId && roomId != currentRoomId) {
            currentRoomId = roomId;
            loadChatRoom(roomId);
            updateSidebarActiveState(roomId);
        }
    });
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Load chat rooms sidebar with loader on initial load
        loadChatRoomsSidebar(true);
        
        // Attach click handler to chat title
        const chatTitleContainer = document.getElementById('chatTitleContainer');
        if (chatTitleContainer) {
            chatTitleContainer.addEventListener('click', function() {
                if (currentRoomData) {
                    toggleRightSidebar();
                }
            });
        }
        
        // Attach click handler to close sidebar button
        const closeSidebarBtn = document.getElementById('closeSidebarBtn');
        if (closeSidebarBtn) {
            closeSidebarBtn.addEventListener('click', function() {
                closeRightSidebar();
            });
        }
        
        // Attach click handler to backdrop (close sidebar on backdrop click)
        const backdrop = document.getElementById('sidebarBackdrop');
        if (backdrop) {
            backdrop.addEventListener('click', function() {
                closeRightSidebar();
            });
        }
        
        // Initialize chat area to full width (sidebar hidden by default)
        const chatArea = document.getElementById('chatArea');
        if (chatArea) {
            chatArea.classList.add('chat-area-full');
        }
        
        // Load chat room if roomId is provided
        if (currentRoomId) {
            loadChatRoom(currentRoomId);
        } else {
            document.getElementById('welcomeTitle').textContent = 'Please select a chat';
            document.getElementById('welcomeText').textContent = 'Choose a conversation from the left to start messaging';
        }
    });

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
        transition: opacity 0.3s ease;
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
        cursor: pointer;
    }
    
    .chat-room-item:hover {
        background: #f3f4f6 !important;
        border-left-color: #667eea;
        transform: translateX(2px);
    }
    
    .chat-room-item.active {
        background: #eef2ff !important;
        border-left-color: #667eea;
        font-weight: 600;
    }
    
    .chat-room-item.active .chat-room-name {
        color: #667eea;
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
    
    /* Sidebar Loader */
    #sidebarLoader {
        transition: opacity 0.3s ease;
    }
    
    #sidebarLoader.hidden {
        display: none;
    }
    
     /* Chat Area Width Control */
     .chat-area {
         transition: flex 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                     width 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                     max-width 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
         will-change: flex, width, max-width;
     }
     
     .chat-area-full {
         flex: 1 1 0 !important;
         min-width: 0 !important;
         width: auto !important;
         max-width: none !important;
     }
     
     .chat-area-with-sidebar {
         flex: 0 0 41.66666667% !important; /* col-md-5 default width */
         max-width: 41.66666667% !important;
         width: 41.66666667% !important;
     }
     
     /* Ensure row uses flexbox */
     .chat-container .row.g-0 {
         display: flex;
         flex-wrap: nowrap;
         overflow: hidden;
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
        transition: opacity 0.2s ease;
    }
    
    .chat-title-clickable {
        position: relative;
        padding: 0.25rem 0;
        border-radius: 0.25rem;
        transition: background-color 0.2s ease;
    }
    
    .chat-title-clickable:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    .chat-title-clickable:hover .chat-room-title {
        opacity: 0.9;
    }
    
    .chat-title-info-icon {
        transition: opacity 0.2s ease, transform 0.2s ease;
    }
    
    .chat-title-clickable:hover .chat-title-info-icon {
        opacity: 1;
        transform: scale(1.1);
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
        transition: opacity 0.3s ease;
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
        transition: opacity 0.3s ease;
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
    
    /* Sidebar Backdrop */
    .sidebar-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1049;
        transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        pointer-events: none;
    }
    
    .sidebar-backdrop-hidden {
        opacity: 0;
        visibility: hidden;
    }
    
    .sidebar-backdrop-visible {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }
    
    .sidebar-backdrop-visible {
        cursor: pointer;
    }
    
     /* Right Sidebar Styles */
     .right-sidebar {
         background: white;
         box-shadow: -2px 0 10px rgba(0,0,0,0.05);
         position: relative;
         overflow: hidden;
         will-change: transform, flex, width, max-width, opacity;
         transform-origin: right center;
     }
     
     /* Sidebar Hidden State - Slides out to the right smoothly */
     .right-sidebar.sidebar-hidden {
         flex: 0 0 0 !important;
         width: 0 !important;
         max-width: 0 !important;
         min-width: 0 !important;
         padding: 0 !important;
         margin: 0 !important;
         border: none !important;
         opacity: 0;
         pointer-events: none;
         overflow: hidden;
         transition: flex 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                     width 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                     max-width 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                     padding 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                     margin 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                     opacity 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
     }
     
     /* Sidebar Visible State - Slides in from the right smoothly */
     .right-sidebar.sidebar-visible {
         flex: 0 0 33.333333% !important;
         width: 33.333333% !important;
         max-width: 33.333333% !important;
         opacity: 1;
         pointer-events: auto;
         transition: flex 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                     width 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                     max-width 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                     padding 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                     margin 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                     opacity 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) 0.1s;
         border-left: 1px solid #e5e7eb !important;
         overflow-y: auto;
     }
    
    /* Sidebar Content Animation - Fade in after slide */
    .right-sidebar .sidebar-header,
    .right-sidebar #sidebarContent {
        transition: opacity 0.25s ease 0.15s;
    }
    
    .right-sidebar.sidebar-hidden .sidebar-header,
    .right-sidebar.sidebar-hidden #sidebarContent {
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    
    .right-sidebar.sidebar-visible .sidebar-header,
    .right-sidebar.sidebar-visible #sidebarContent {
        opacity: 1;
    }
    
    .sidebar-header {
        padding: 1.25rem;
        border-bottom: 1px solid #e5e7eb;
        background: white;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .sidebar-content-wrapper {
        padding: 1.5rem;
    }
    
    /* User Details Styles */
    .user-details-header {
        text-align: center;
        padding: 2rem 1rem;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }
    
    .user-details-avatar-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    .user-details-avatar-text-large {
        color: white;
        font-size: 2.5rem;
        font-weight: 600;
    }
    
    .user-details-name {
        color: #1f2937;
        font-weight: 600;
        margin-bottom: 0.5rem;
        font-size: 1.5rem;
    }
    
    .user-details-email {
        color: #6b7280;
        font-size: 0.95rem;
        margin-bottom: 0;
    }
    
    .user-details-body {
        padding-top: 1rem;
    }
    
    .user-details-section {
        margin-bottom: 2rem;
    }
    
    .user-details-section-title {
        color: #374151;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .user-details-item {
        padding: 1rem 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .user-details-item:last-child {
        border-bottom: none;
    }
    
    .user-details-item-label {
        color: #6b7280;
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
    }
    
    .user-details-item-value {
        color: #1f2937;
        font-weight: 500;
        font-size: 0.95rem;
    }
    
    /* Group Details Styles */
    .group-details-header {
        text-align: center;
        padding: 2rem 1rem;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }
    
    .group-details-avatar-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    .group-details-avatar-text-large {
        color: white;
        font-size: 2.5rem;
        font-weight: 600;
    }
    
    .group-details-name {
        color: #1f2937;
        font-weight: 600;
        margin-bottom: 0.5rem;
        font-size: 1.5rem;
    }
    
    .group-details-description {
        color: #6b7280;
        font-size: 0.95rem;
        margin-bottom: 1rem;
    }
    
    .group-details-meta {
        display: flex;
        justify-content: center;
        gap: 1.5rem;
        flex-wrap: wrap;
    }
    
    .group-details-meta-item {
        color: #6b7280;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
    }
    
    .group-details-body {
        padding-top: 1rem;
    }
    
    .group-details-section {
        margin-bottom: 2rem;
    }
    
    .group-details-section-title {
        color: #374151;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
    }
    
    .members-list-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .member-item-clickable {
        padding: 1rem;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    
    .member-item-clickable:hover {
        background: #f9fafb;
        border-color: #e5e7eb;
        transform: translateX(4px);
    }
    
    .member-arrow {
        color: #9ca3af;
        font-size: 1.2rem;
        transition: transform 0.2s ease;
    }
    
    .member-item-clickable:hover .member-arrow {
        transform: translateX(4px);
        color: #667eea;
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
        .col-md-3 {
            display: none !important;
        }
        .sidebar-backdrop {
            display: block;
        }
        .right-sidebar {
            position: fixed;
            right: 0;
            top: 0;
            bottom: 0;
            z-index: 1050;
            box-shadow: -4px 0 20px rgba(0,0,0,0.15);
            width: 85% !important;
            max-width: 400px !important;
            flex: 0 0 auto !important;
            will-change: transform, opacity;
        }
        .right-sidebar.sidebar-hidden {
            transform: translateX(100%);
            opacity: 0;
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                        opacity 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .right-sidebar.sidebar-visible {
            transform: translateX(0);
            opacity: 1;
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                        opacity 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .chat-area,
        .chat-area-full,
        .chat-area-with-sidebar {
            width: 100% !important;
            flex: 1 1 100% !important;
            max-width: 100% !important;
        }
    }
    
    @media (min-width: 769px) and (max-width: 992px) {
        .sidebar-backdrop {
            display: block;
        }
        .right-sidebar {
            position: fixed;
            right: 0;
            top: 0;
            bottom: 0;
            z-index: 1050;
            box-shadow: -4px 0 20px rgba(0,0,0,0.15);
            width: 350px !important;
            max-width: 350px !important;
            flex: 0 0 auto !important;
            will-change: transform, opacity;
        }
        .right-sidebar.sidebar-hidden {
            transform: translateX(100%);
            opacity: 0;
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                        opacity 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .right-sidebar.sidebar-visible {
            transform: translateX(0);
            opacity: 1;
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                        opacity 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .chat-area-full {
            flex: 1 1 auto !important;
            width: auto !important;
            max-width: none !important;
        }
    }
    
     @media (min-width: 993px) {
         .sidebar-backdrop {
             display: none !important;
         }
         /* Desktop: Sidebar maintains width but slides out of view */
         .right-sidebar {
             position: relative;
         }
         .right-sidebar.sidebar-hidden {
             /* Keep width but slide out */
             transform: translateX(100%);
             opacity: 0;
             pointer-events: none;
         }
         .right-sidebar.sidebar-visible {
             transform: translateX(0);
             opacity: 1;
             pointer-events: auto;
         }
         /* Hide sidebar content when hidden to prevent interaction */
         .right-sidebar.sidebar-hidden * {
             pointer-events: none;
         }
     }
</style>

<script>
// Handle create room form submission via API
document.addEventListener('DOMContentLoaded', function() {
    const createRoomForm = document.getElementById('createRoomFormInRoom');
    if (createRoomForm) {
        const apiBaseUrl = '/api/chat';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        createRoomForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                name: document.getElementById('room_name_modal').value,
                description: document.getElementById('room_description_modal').value,
                user_ids: Array.from(document.getElementById('user_ids_modal').selectedOptions).map(opt => opt.value)
            };
            
            fetch(`${apiBaseUrl}/rooms`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('createRoomModal'));
                    if (modal) modal.hide();
                    
                    // Reset form
                    createRoomForm.reset();
                    
                    // Redirect to new room
                    if (data.room && data.room.id) {
                        window.location.href = `/chat/room/${data.room.id}`;
                    } else {
                        // Reload page to show new room
                        window.location.reload();
                    }
                } else {
                    alert('Failed to create room: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error creating room:', error);
                alert('Failed to create room. Please try again.');
            });
        });
    }
});
</script>
@endsection
