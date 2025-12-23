<?php

use ChatPackage\ChatPackage\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Chat Package API Routes
|--------------------------------------------------------------------------
|
| These routes are API-based and return JSON responses.
| Perfect for use with React.js, Vue.js, or any frontend framework.
| All routes are prefixed with /api/chat
|
*/

// Authentication is handled by the service provider (web + auth middleware)
// These routes work with both web (session) and API (token) authentication
// Get all chat rooms for authenticated user
Route::get('/rooms', [ChatController::class, 'apiGetUserChatRooms'])->name('rooms.index');

// Get a specific chat room with messages
Route::get('/rooms/{roomId}', [ChatController::class, 'apiGetChatRoom'])->name('rooms.show');

// Create a new chat room
Route::post('/rooms', [ChatController::class, 'apiCreateChatRoom'])->name('rooms.create');

// Update a chat room
Route::put('/rooms/{roomId}', [ChatController::class, 'apiUpdateChatRoom'])->name('rooms.update');

// Delete a chat room
Route::delete('/rooms/{roomId}', [ChatController::class, 'apiDeleteChatRoom'])->name('rooms.delete');

// Get messages for a chat room
Route::get('/rooms/{roomId}/messages', [ChatController::class, 'apiGetMessages'])->name('rooms.messages');

// Send a message to a chat room
Route::post('/rooms/{roomId}/messages', [ChatController::class, 'apiSendMessage'])->name('rooms.messages.send');

// Get peers (users) in a chat room
Route::get('/rooms/{roomId}/peers', [ChatController::class, 'apiGetPeers'])->name('rooms.peers');

// Mark messages as read
Route::post('/rooms/{roomId}/mark-read', [ChatController::class, 'apiMarkMessagesAsRead'])->name('rooms.mark-read');

// Get unread message counts
Route::get('/unread-counts', [ChatController::class, 'apiGetUnreadCounts'])->name('unread-counts');

// Peer-to-peer chat endpoints
Route::get('/p2p/{userId}', [ChatController::class, 'apiGetPeerToPeerChatInfo'])->name('p2p.info');
Route::post('/p2p/{userId}/message', [ChatController::class, 'apiSendPeerToPeerMessage'])->name('p2p.message');
Route::post('/p2p/{userId}/start', [ChatController::class, 'apiStartPeerToPeerChat'])->name('p2p.start');

// User status endpoints
Route::post('/user/status', [ChatController::class, 'apiUpdateUserStatus'])->name('user.status');

