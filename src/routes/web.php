<?php

/**
 * ============================================================================
 * COMPLETELY DEPRECATED: Web Routes for Chat Package
 * ============================================================================
 * 
 * ⚠️  WARNING: This file is NO LONGER USED!
 * 
 * This package is now 100% API-based. Web routes are COMPLETELY REMOVED.
 * This file exists only for reference and will NOT be loaded.
 * 
 * All functionality is available via API routes at /api/chat/*
 * 
 * If you need to use the Blade views, you must:
 * 1. Create your own routes in your application's routes/web.php
 * 2. Use JavaScript in the views to call the API endpoints
 * 3. The views are available at: packages/chat-package/src/resources/views/
 * 
 * Example API endpoints:
 * - GET  /api/chat/rooms - Get all chat rooms
 * - GET  /api/chat/rooms/{roomId} - Get specific room
 * - POST /api/chat/rooms - Create room
 * - POST /api/chat/rooms/{roomId}/messages - Send message
 * 
 * ============================================================================
 */

// This file is kept for reference only and is never loaded.
// The ChatPackageServiceProvider does NOT load this file.
// All routes have been moved to routes/api.php

use ChatPackage\ChatPackage\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

// ⚠️ THESE ROUTES ARE NOT LOADED - FOR REFERENCE ONLY ⚠️
// Route::get('/', [ChatController::class, 'index'])->name('index');
// Route::get('/room/{roomId}', [ChatController::class, 'show'])->name('show');
// Route::post('/room/create', [ChatController::class, 'createRoom'])->name('room.create');
// Route::post('/room/{roomId}/message', [ChatController::class, 'sendMessage'])->name('message.send');
// Route::get('/room/{roomId}/messages', [ChatController::class, 'getMessages'])->name('messages.get');
// Route::get('/room/{roomId}/peers', [ChatController::class, 'getPeers'])->name('peers.get');
// Route::post('/start/{userId}', [ChatController::class, 'startPeerToPeerChat'])->name('start.p2p');
// Route::get('/api/p2p/{userId}', [ChatController::class, 'getPeerToPeerChatInfo'])->name('api.p2p.info');
// Route::post('/api/p2p/{userId}/message', [ChatController::class, 'sendPeerToPeerMessage'])->name('api.p2p.message');

