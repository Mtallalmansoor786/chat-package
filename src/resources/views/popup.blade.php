@if(config('chat-package.popup_enabled', true))
<!-- Chat Popup Modal - Sticky to Bottom -->
<div id="chatPopup" class="chat-popup-container" style="display: none;">
    <div class="chat-popup-wrapper">
        <!-- Header -->
        <div class="chat-popup-header">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="mb-0 fw-bold">New message</h6>
                    <span id="recipientTag" class="recipient-tag" style="display: none;">
                        <span id="recipientName"></span>
                        <button type="button" class="btn-close-tag" onclick="closeChatPopup()">
                            <i class="bi bi-x"></i>
                        </button>
                    </span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-link text-dark" onclick="toggleChatPopupSize()" title="Maximize/Restore">
                        <i class="bi bi-arrows-angle-contract" id="resizeIcon"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-link text-dark" onclick="closeChatPopup()" title="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Recipient Info Section -->
        <div id="recipientInfo" class="chat-popup-recipient" style="display: none;">
            <div class="d-flex align-items-center gap-3">
                <div class="recipient-avatar">
                    <span id="recipientAvatarText"></span>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 fw-bold" id="recipientFullName"></h6>
                    </div>
                    <p class="mb-0 text-muted small" id="recipientEmail"></p>
                </div>
            </div>
        </div>

        <!-- Messages History (if exists) -->
        <div id="messagesHistory" class="chat-popup-messages" style="display: none;">
            <div id="messagesContainer" class="messages-list">
                <!-- Messages will be loaded here -->
            </div>
        </div>

        <!-- Message Input Area -->
        <div class="chat-popup-input-area">
            <textarea 
                id="messageTextarea" 
                class="chat-popup-textarea" 
                placeholder="Write a message..."
                rows="2"
            ></textarea>
        </div>

        <!-- Footer Actions -->
        <div class="chat-popup-footer">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="btn btn-sm btn-link text-muted" title="Image">
                        <i class="bi bi-image"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-link text-muted" title="Attachment">
                        <i class="bi bi-paperclip"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-link text-muted" title="GIF">
                        GIF
                    </button>
                    <button type="button" class="btn btn-sm btn-link text-muted" title="Emoji">
                        <i class="bi bi-emoji-smile"></i>
                    </button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-link text-muted" title="More options">
                        <i class="bi bi-three-dots"></i>
                    </button>
                    <button type="button" id="sendMessageBtn" class="btn btn-sm btn-primary" onclick="sendPopupMessage()" disabled>
                        Send
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .chat-popup-container {
        position: fixed;
        bottom: 0;
        right: 20px;
        width: 400px;
        max-height: 600px;
        z-index: 1050;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    }

    .chat-popup-container.maximized {
        width: 500px;
        max-height: 80vh;
    }

    .chat-popup-wrapper {
        background: white;
        border-radius: 8px 8px 0 0;
        display: flex;
        flex-direction: column;
        height: 100%;
        max-height: 600px;
        overflow: hidden;
    }

    .chat-popup-container.maximized .chat-popup-wrapper {
        max-height: 80vh;
    }

    .chat-popup-header {
        background: #f8f9fa;
        padding: 12px 16px;
        border-bottom: 1px solid #dee2e6;
        border-radius: 8px 8px 0 0;
    }

    .recipient-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #28a745;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .btn-close-tag {
        background: none;
        border: none;
        color: white;
        padding: 0;
        margin: 0;
        cursor: pointer;
        display: flex;
        align-items: center;
        font-size: 0.9rem;
    }

    .chat-popup-recipient {
        padding: 16px;
        border-bottom: 1px solid #dee2e6;
        background: #f8f9fa;
    }

    .recipient-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .chat-popup-messages {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        background: #f8f9fa;
        min-height: 200px;
        max-height: 300px;
    }

    .chat-popup-container.maximized .chat-popup-messages {
        max-height: 50vh;
    }

    .messages-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .message-item {
        padding: 10px 14px;
        border-radius: 8px;
        max-width: 80%;
        word-wrap: break-word;
    }

    .message-item.own {
        background: #667eea;
        color: white;
        align-self: flex-end;
        border-bottom-right-radius: 4px;
    }

    .message-item.other {
        background: white;
        color: #1f2937;
        align-self: flex-start;
        border-bottom-left-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .message-time {
        font-size: 0.7rem;
        opacity: 0.7;
        margin-top: 4px;
    }

    .chat-popup-input-area {
        padding: 12px 16px;
        border-top: 1px solid #dee2e6;
        background: white;
    }

    .chat-popup-textarea {
        width: 100%;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 8px 12px;
        resize: none;
        font-size: 0.9rem;
        line-height: 1.4;
        transition: border-color 0.3s ease;
        min-height: 38px;
        max-height: 100px;
        overflow-y: auto;
    }

    .chat-popup-textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .chat-popup-footer {
        padding: 10px 16px;
        border-top: 1px solid #dee2e6;
        background: white;
    }

    .chat-popup-footer .btn-link {
        padding: 4px 8px;
        text-decoration: none;
    }

    .chat-popup-footer .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 6px 16px;
        border-radius: 6px;
        font-weight: 500;
    }

    .chat-popup-footer .btn-primary:disabled {
        background: #e5e7eb;
        color: #9ca3af;
        cursor: not-allowed;
    }

    /* Scrollbar styling */
    .chat-popup-messages::-webkit-scrollbar,
    .chat-popup-textarea::-webkit-scrollbar {
        width: 6px;
    }

    .chat-popup-messages::-webkit-scrollbar-track,
    .chat-popup-textarea::-webkit-scrollbar-track {
        background: transparent;
    }

    .chat-popup-messages::-webkit-scrollbar-thumb,
    .chat-popup-textarea::-webkit-scrollbar-thumb {
        background: rgba(0,0,0,0.2);
        border-radius: 3px;
    }
</style>

<script>
    let currentChatRoomId = null;
    let currentUserId = null;
    let currentOtherUserId = null;
    let chatExists = false;
    let isMaximized = false;

    function openChatPopup(userId, userName, userEmail) {
        currentUserId = {{ Auth::id() }};
        currentOtherUserId = userId;
        const popup = document.getElementById('chatPopup');
        const recipientTag = document.getElementById('recipientTag');
        const recipientName = document.getElementById('recipientName');
        const recipientInfo = document.getElementById('recipientInfo');
        const recipientFullName = document.getElementById('recipientFullName');
        const recipientEmail = document.getElementById('recipientEmail');
        const recipientAvatarText = document.getElementById('recipientAvatarText');
        const messagesHistory = document.getElementById('messagesHistory');
        const messagesContainer = document.getElementById('messagesContainer');
        
        // Set recipient info
        recipientName.textContent = userName;
        recipientFullName.textContent = userName;
        recipientEmail.textContent = userEmail;
        recipientAvatarText.textContent = userName.substring(0, 2).toUpperCase();
        
        // Show popup and recipient info
        popup.style.display = 'block';
        recipientTag.style.display = 'inline-flex';
        recipientInfo.style.display = 'block';
        
        // Clear previous messages
        messagesContainer.innerHTML = '';
        messagesHistory.style.display = 'none';
        
        // Reset chat state
        currentChatRoomId = null;
        chatExists = false;
        
        // Enable send button (will be used to create chat on first message)
        document.getElementById('sendMessageBtn').disabled = false;
        
        // Load chat info and messages (only if chat exists)
        fetch(`/chat/api/p2p/${userId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Chat API Response:', data); // Debug log
            if (data.success) {
                chatExists = data.chat_exists;
                
                if (chatExists && data.chat_room) {
                    currentChatRoomId = data.chat_room.id;
                    
                    // Always show messages history area if chat exists
                    messagesHistory.style.display = 'block';
                    
                    // Display messages (even if empty array, to show empty state)
                    if (data.messages && Array.isArray(data.messages) && data.messages.length > 0) {
                        console.log('Displaying messages:', data.messages.length); // Debug log
                        displayMessages(data.messages);
                    } else {
                        console.log('No messages found'); // Debug log
                        // Show empty state if no messages
                        messagesContainer.innerHTML = '<div class="text-center text-muted py-4"><small>No messages yet. Start the conversation!</small></div>';
                    }
                } else {
                    // No chat exists yet - messages area stays hidden until first message
                    messagesHistory.style.display = 'none';
                }
            } else {
                console.error('Failed to load chat:', data.error);
                messagesHistory.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error loading chat:', error);
            messagesHistory.style.display = 'none';
        });
    }

    function displayMessages(messages) {
        const container = document.getElementById('messagesContainer');
        if (!container) {
            console.error('Messages container not found');
            return;
        }
        
        container.innerHTML = '';
        
        if (!messages || !Array.isArray(messages) || messages.length === 0) {
            container.innerHTML = '<div class="text-center text-muted py-4"><small>No messages yet. Start the conversation!</small></div>';
            return;
        }
        
        console.log('Rendering messages:', messages.length); // Debug log
        
        messages.forEach((message, index) => {
            try {
                const isOwn = parseInt(message.user_id) === parseInt(currentUserId);
                const messageDiv = document.createElement('div');
                messageDiv.className = `message-item ${isOwn ? 'own' : 'other'}`;
                
                const time = new Date(message.created_at).toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                messageDiv.innerHTML = `
                    <div class="message-content">${escapeHtml(message.message || '')}</div>
                    <div class="message-time">${time}</div>
                `;
                
                container.appendChild(messageDiv);
            } catch (error) {
                console.error('Error rendering message:', error, message);
            }
        });
        
        // Scroll to bottom after a short delay to ensure DOM is updated
        setTimeout(() => {
            const messagesHistory = document.getElementById('messagesHistory');
            if (messagesHistory) {
                messagesHistory.scrollTop = messagesHistory.scrollHeight;
            }
        }, 100);
    }

    function sendPopupMessage() {
        const textarea = document.getElementById('messageTextarea');
        const message = textarea.value.trim();
        
        if (!message) {
            return;
        }
        
        // Disable send button
        const sendBtn = document.getElementById('sendMessageBtn');
        sendBtn.disabled = true;
        
        // Determine which endpoint to use
        let url, body;
        
        if (chatExists && currentChatRoomId) {
            // Chat exists, use regular message endpoint
            url = `/chat/room/${currentChatRoomId}/message`;
            body = JSON.stringify({ message: message });
        } else {
            // Chat doesn't exist, use P2P message endpoint (will create chat)
            url = `/chat/api/p2p/${currentOtherUserId}/message`;
            body = JSON.stringify({ message: message });
        }
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: body
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear textarea
                textarea.value = '';
                
                // Auto-resize textarea
                textarea.style.height = 'auto';
                
                // If this was the first message, update chat state
                if (!chatExists && data.chat_room) {
                    chatExists = true;
                    currentChatRoomId = data.chat_room.id;
                }
                
                // Add message to display
                const messagesHistory = document.getElementById('messagesHistory');
                if (messagesHistory.style.display === 'none') {
                    messagesHistory.style.display = 'block';
                }
                
                const container = document.getElementById('messagesContainer');
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message-item own';
                
                const time = new Date(data.message.created_at).toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                messageDiv.innerHTML = `
                    <div class="message-content">${escapeHtml(data.message.message)}</div>
                    <div class="message-time">${time}</div>
                `;
                
                container.appendChild(messageDiv);
                
                // Scroll to bottom
                messagesHistory.scrollTop = messagesHistory.scrollHeight;
            } else {
                alert('Failed to send message: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
            alert('Failed to send message. Please try again.');
        })
        .finally(() => {
            // Re-enable send button
            sendBtn.disabled = false;
            textarea.focus();
        });
    }

    function closeChatPopup() {
        document.getElementById('chatPopup').style.display = 'none';
        document.getElementById('messageTextarea').value = '';
        const textarea = document.getElementById('messageTextarea');
        if (textarea) {
            textarea.style.height = 'auto';
        }
        currentChatRoomId = null;
        currentOtherUserId = null;
        chatExists = false;
    }

    function toggleChatPopupSize() {
        const popup = document.getElementById('chatPopup');
        const resizeIcon = document.getElementById('resizeIcon');
        
        isMaximized = !isMaximized;
        
        if (isMaximized) {
            popup.classList.add('maximized');
            resizeIcon.className = 'bi bi-arrows-angle-expand';
        } else {
            popup.classList.remove('maximized');
            resizeIcon.className = 'bi bi-arrows-angle-contract';
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Enable send button when textarea has content and auto-resize
    document.addEventListener('DOMContentLoaded', function() {
        const textarea = document.getElementById('messageTextarea');
        const sendBtn = document.getElementById('sendMessageBtn');
        
        if (textarea) {
            // Auto-resize textarea
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 100) + 'px';
                
                if (this.value.trim()) {
                    sendBtn.disabled = false;
                } else {
                    sendBtn.disabled = true;
                }
            });
            
            // Allow Enter to send (Shift+Enter for new line)
            textarea.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if (this.value.trim()) {
                        sendPopupMessage();
                    }
                }
            });
        }
    });
</script>
@endif

