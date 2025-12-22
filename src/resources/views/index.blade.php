@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="bi bi-chat-dots me-2"></i>Chat Rooms
                        </h5>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createRoomModal">
                            <i class="bi bi-plus-circle me-1"></i>Create Room
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="chatRoomsList">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading chat rooms...</p>
                        </div>
                    </div>
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
            <form id="createRoomForm">
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
                        <select class="form-select" id="user_ids" name="user_ids[]" multiple required>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const apiBaseUrl = '/api/chat';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Load chat rooms
    function loadChatRooms() {
        fetch(`${apiBaseUrl}/rooms`, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('API Response:', data); // Debug log
            const container = document.getElementById('chatRoomsList');
            
            // Check for chat_rooms (API response format)
            const rooms = data.chat_rooms || data.rooms || [];
            
            if (data.success && rooms && rooms.length > 0) {
                container.innerHTML = '<div class="list-group">' + rooms.map(room => `
                    <a href="/chat/room/${room.id}" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1 fw-bold">${room.display_name || room.name || 'Chat Room'}</h6>
                            <small>${room.updated_at || ''}</small>
                        </div>
                        ${room.description ? `<p class="mb-1 text-muted small">${room.description.substring(0, 100)}</p>` : ''}
                        <div class="d-flex align-items-center mt-2">
                            <small class="text-muted me-3">
                                <i class="bi bi-people me-1"></i>${room.members_count || room.member_count || 0} members
                            </small>
                            ${room.last_message ? `<small class="text-muted">
                                <i class="bi bi-chat me-1"></i>Last message: ${new Date(room.last_message.created_at).toLocaleString()}
                            </small>` : ''}
                        </div>
                    </a>
                `).join('') + '</div>';
            } else {
                container.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-chat-dots fs-1 text-muted d-block mb-3"></i>
                        <p class="text-muted">No chat rooms yet. Create your first room to start chatting!</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading chat rooms:', error);
            const container = document.getElementById('chatRoomsList');
            container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Failed to load chat rooms. 
                    <br><small>Error: ${error.message}</small>
                    <br><button onclick="location.reload()" class="btn btn-sm btn-outline-danger mt-2">Refresh Page</button>
                </div>
            `;
        });
    }
    
    // Load users for create room form
    function loadUsers() {
        // Try to fetch users from /users endpoint or use a simple approach
        // For now, we'll fetch from the users page endpoint if available
        fetch('/users', {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (response.ok) {
                return response.text().then(html => {
                    // Parse HTML to extract users (simple approach)
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const userCards = doc.querySelectorAll('.user-card');
                    const select = document.getElementById('user_ids');
                    
                    if (userCards.length > 0) {
                        select.innerHTML = '';
                        userCards.forEach(card => {
                            const userId = card.querySelector('button')?.getAttribute('onclick')?.match(/(\d+)/)?.[1];
                            const userName = card.querySelector('.user-name')?.textContent?.trim();
                            const userEmail = card.querySelector('.user-email')?.textContent?.trim();
                            
                            if (userId && userName) {
                                const option = document.createElement('option');
                                option.value = userId;
                                option.textContent = `${userName}${userEmail ? ' (' + userEmail + ')' : ''}`;
                                select.appendChild(option);
                            }
                        });
                    } else {
                        select.innerHTML = '<option value="">No users available</option>';
                    }
                });
            } else {
                document.getElementById('user_ids').innerHTML = '<option value="">Unable to load users</option>';
            }
        })
        .catch(error => {
            console.error('Error loading users:', error);
            document.getElementById('user_ids').innerHTML = '<option value="">Unable to load users</option>';
        });
    }
    
    // Handle create room form
    document.getElementById('createRoomForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            name: document.getElementById('room_name').value,
            description: document.getElementById('room_description').value,
            user_ids: Array.from(document.getElementById('user_ids').selectedOptions).map(opt => opt.value)
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
                modal.hide();
                
                // Reset form
                document.getElementById('createRoomForm').reset();
                
                // Reload chat rooms
                loadChatRooms();
            } else {
                alert('Failed to create room: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error creating room:', error);
            alert('Failed to create room. Please try again.');
        });
    });
    
    // Initial load
    loadChatRooms();
    loadUsers();
});
</script>
@endsection

