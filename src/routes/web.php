<?php

use ChatPackage\ChatPackage\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ChatController::class, 'index'])->name('index');
Route::get('/room/{roomId}', [ChatController::class, 'show'])->name('show');
Route::post('/room/create', [ChatController::class, 'createRoom'])->name('room.create');
Route::post('/room/{roomId}/message', [ChatController::class, 'sendMessage'])->name('message.send');
Route::get('/room/{roomId}/messages', [ChatController::class, 'getMessages'])->name('messages.get');
Route::get('/room/{roomId}/peers', [ChatController::class, 'getPeers'])->name('peers.get');
Route::post('/start/{userId}', [ChatController::class, 'startPeerToPeerChat'])->name('start.p2p');
Route::get('/api/p2p/{userId}', [ChatController::class, 'getPeerToPeerChatInfo'])->name('api.p2p.info');
Route::post('/api/p2p/{userId}/message', [ChatController::class, 'sendPeerToPeerMessage'])->name('api.p2p.message');

