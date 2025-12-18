<?php

namespace ChatPackage\ChatPackage\Http\Controllers;

use ChatPackage\ChatPackage\Http\Requests\CreateChatRoomRequest;
use ChatPackage\ChatPackage\Http\Requests\SendMessageRequest;
use ChatPackage\ChatPackage\Services\Contracts\ChatServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function __construct(
        private ChatServiceInterface $chatService
    ) {
    }

    /**
     * Display the chat interface.
     */
    public function index(): View
    {
        $chatRooms = $this->chatService->getUserChatRooms(Auth::id());
        $chatRoom = null;
        $messages = collect([]);
        $unreadCounts = $this->chatService->getUnreadCounts(Auth::id(), $chatRooms);
        $firstUnreadMessageId = null;

        return view('chat-package::room', compact('chatRoom', 'messages', 'chatRooms', 'unreadCounts', 'firstUnreadMessageId'));
    }

    /**
     * Show a specific chat room.
     */
    public function show(int $roomId): View
    {
        $userId = Auth::id();
        $chatRoom = $this->chatService->getChatRoom($roomId, $userId);
        $messages = $this->chatService->getMessages($roomId, $userId);
        
        // Get first unread message ID for scrolling BEFORE marking as read
        $firstUnreadMessageId = $this->chatService->getFirstUnreadMessageId($roomId, $userId);
        
        // Mark all messages in this room as read
        $this->chatService->markMessagesAsRead($roomId, $userId);
        
        $chatRooms = $this->chatService->getUserChatRooms($userId);
        $unreadCounts = $this->chatService->getUnreadCounts($userId, $chatRooms);

        return view('chat-package::room', compact('chatRoom', 'messages', 'chatRooms', 'unreadCounts', 'firstUnreadMessageId'));
    }

    /**
     * Create a new chat room.
     */
    public function createRoom(CreateChatRoomRequest $request)
    {
        $chatRoom = $this->chatService->createChatRoom($request->validated(), Auth::id());

        return redirect()->route('chat.show', $chatRoom->id)
            ->with('success', 'Chat room created successfully!');
    }

    /**
     * Send a message.
     */
    public function sendMessage(SendMessageRequest $request, int $roomId): JsonResponse
    {
        $message = $this->chatService->sendMessage($roomId, Auth::id(), $request->validated()['message']);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'chat_room_id' => $message->chat_room_id,
                'user_id' => $message->user_id,
                'message' => $message->message,
                'type' => $message->type,
                'created_at' => $message->created_at->toDateTimeString(),
                'user' => [
                    'id' => $message->user->id,
                    'name' => $message->user->name,
                    'email' => $message->user->email,
                ],
            ],
        ]);
    }

    /**
     * Get messages for a chat room.
     */
    public function getMessages(int $roomId): JsonResponse
    {
        $messages = $this->chatService->getMessages($roomId, Auth::id());

        return response()->json($messages);
    }

    /**
     * Get peers (users) for a chat room.
     */
    public function getPeers(int $roomId): JsonResponse
    {
        $peers = $this->chatService->getPeers($roomId, Auth::id());

        return response()->json([
            'peers' => $peers,
        ]);
    }
}
