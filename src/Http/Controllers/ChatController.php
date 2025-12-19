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
     * Send a P2P message. Creates chat if it doesn't exist.
     */
    public function sendPeerToPeerMessage(int $userId): JsonResponse
    {
        $currentUserId = Auth::id();
        
        try {
            // Find or create P2P chat
            $chatRoom = $this->chatService->findOrCreatePeerToPeerChat($currentUserId, $userId);
            
            // Get message from request
            $request = request();
            $messageText = $request->input('message');
            
            if (empty($messageText)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Message cannot be empty.',
                ], 400);
            }
            
            // Send message
            $message = $this->chatService->sendMessage($chatRoom->id, $currentUserId, $messageText);

            return response()->json([
                'success' => true,
                'chat_room' => [
                    'id' => $chatRoom->id,
                    'name' => $chatRoom->name,
                    'is_peer_to_peer' => $chatRoom->isPeerToPeer(),
                ],
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
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to send message. Please try again.',
            ], 500);
        }
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

    /**
     * Start or continue a peer-to-peer chat with another user.
     * Finds existing P2P chat or creates a new one, then redirects to chat room.
     */
    public function startPeerToPeerChat(int $userId)
    {
        $currentUserId = Auth::id();

        try {
            $chatRoom = $this->chatService->findOrCreatePeerToPeerChat($currentUserId, $userId);
            
            return redirect()->route('chat.show', $chatRoom->id)
                ->with('success', 'Chat started successfully!');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to start chat. Please try again.');
        }
    }

    /**
     * Get P2P chat info and messages (API endpoint for popup).
     * Only returns chat if it exists. Does not create new chat.
     */
    public function getPeerToPeerChatInfo(int $userId): JsonResponse
    {
        $currentUserId = Auth::id();

        try {
            // Only find existing P2P chat, don't create
            $chatRoom = $this->chatService->findPeerToPeerChat($currentUserId, $userId);
            
            // If no chat exists, return empty response
            if (!$chatRoom) {
                $userModel = config('auth.providers.users.model', \App\Models\User::class);
                $otherUser = $userModel::find($userId);
                
                return response()->json([
                    'success' => true,
                    'chat_exists' => false,
                    'chat_room' => null,
                    'other_peer' => $otherUser ? [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        'email' => $otherUser->email,
                    ] : null,
                    'messages' => [],
                    'has_more' => false,
                ]);
            }
            
            // Get other peer info
            $otherPeer = $chatRoom->getOtherPeer($currentUserId);
            
            // Get last 100 messages
            $messages = $this->chatService->getMessages($chatRoom->id, $currentUserId, 100);
            
            // Mark messages as read
            $this->chatService->markMessagesAsRead($chatRoom->id, $currentUserId);

            // Format messages for JSON response
            $messageItems = $messages->items();
            $formattedMessages = collect($messageItems)->map(function ($message) {
                // Ensure user relationship is loaded
                if (!$message->relationLoaded('user')) {
                    $message->load('user');
                }
                
                return [
                    'id' => $message->id,
                    'chat_room_id' => $message->chat_room_id,
                    'user_id' => $message->user_id,
                    'message' => $message->message ?? '',
                    'type' => $message->type ?? 'text',
                    'created_at' => $message->created_at ? $message->created_at->toDateTimeString() : now()->toDateTimeString(),
                    'user' => $message->user ? [
                        'id' => $message->user->id,
                        'name' => $message->user->name ?? 'Unknown',
                        'email' => $message->user->email ?? '',
                    ] : [
                        'id' => $message->user_id,
                        'name' => 'Unknown',
                        'email' => '',
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'chat_exists' => true,
                'chat_room' => [
                    'id' => $chatRoom->id,
                    'name' => $chatRoom->name,
                    'is_peer_to_peer' => $chatRoom->isPeerToPeer(),
                ],
                'other_peer' => $otherPeer ? [
                    'id' => $otherPeer->id,
                    'name' => $otherPeer->name,
                    'email' => $otherPeer->email,
                ] : null,
                'messages' => $formattedMessages->values()->all(),
                'has_more' => $messages->hasMorePages(),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load chat. Please try again.',
            ], 500);
        }
    }
}
