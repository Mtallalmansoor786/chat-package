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
                    @if($chatRooms->count() > 0)
                        <div class="list-group">
                            @foreach($chatRooms as $room)
                                <a href="{{ route('chat.show', $room->id) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 fw-bold">{{ $room->name }}</h6>
                                        <small>{{ $room->updated_at->diffForHumans() }}</small>
                                    </div>
                                    @if($room->description)
                                        <p class="mb-1 text-muted small">{{ Str::limit($room->description, 100) }}</p>
                                    @endif
                                    <div class="d-flex align-items-center mt-2">
                                        <small class="text-muted me-3">
                                            <i class="bi bi-people me-1"></i>{{ $room->users->count() }} members
                                        </small>
                                        @if($room->messages->count() > 0)
                                            <small class="text-muted">
                                                <i class="bi bi-chat me-1"></i>Last message: {{ $room->messages->first()->created_at->diffForHumans() }}
                                            </small>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-chat-dots fs-1 text-muted d-block mb-3"></i>
                            <p class="text-muted">No chat rooms yet. Create your first room to start chatting!</p>
                        </div>
                    @endif
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
@endsection

