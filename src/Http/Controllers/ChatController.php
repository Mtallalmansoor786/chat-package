<?php

namespace ChatPackage\ChatPackage\Http\Controllers;

use ChatPackage\ChatPackage\Http\Requests\CreateChatRoomRequest;
use ChatPackage\ChatPackage\Http\Requests\SendMessageRequest;
use ChatPackage\ChatPackage\Services\Contracts\ChatServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function __construct(
        private ChatServiceInterface $chatService
    ) {
    }

    /**
     * Display the chat interface.
     * 
     * NOTE: This method is deprecated. Use apiGetUserChatRooms() instead.
     * Web routes are disabled by default - this package is API-only.
     */
    public function index(): JsonResponse
    {
        // Return JSON response instead of view (API-only mode)
        return $this->apiGetUserChatRooms();
    }

    /**
     * Show a specific chat room.
     * 
     * NOTE: This method is deprecated. Use apiGetChatRoom() instead.
     * Web routes are disabled by default - this package is API-only.
     */
    public function show(int $roomId): JsonResponse
    {
        // Return JSON response instead of view (API-only mode)
        return $this->apiGetChatRoom($roomId);
    }

    /**
     * Create a new chat room.
     * 
     * NOTE: This method is deprecated. Use apiCreateChatRoom() instead.
     * Web routes are disabled by default - this package is API-only.
     */
    public function createRoom(CreateChatRoomRequest $request): JsonResponse
    {
        // Return JSON response instead of redirect (API-only mode)
        return $this->apiCreateChatRoom($request);
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
     * 
     * NOTE: This method is deprecated. Use apiStartPeerToPeerChat() instead.
     * Web routes are disabled by default - this package is API-only.
     */
    public function startPeerToPeerChat(int $userId): JsonResponse
    {
        // Return JSON response instead of redirect (API-only mode)
        $request = request();
        return $this->apiStartPeerToPeerChat($request, $userId);
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

    // ============================================
    // API METHODS - For React.js and other frontends
    // ============================================

    /**
     * API: Get all chat rooms for authenticated user.
     */
    public function apiGetUserChatRooms(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $chatRooms = $this->chatService->getUserChatRooms($userId);
            $unreadCounts = $this->chatService->getUnreadCounts($userId, $chatRooms);

            $formattedRooms = $chatRooms->map(function ($room) use ($userId, $unreadCounts) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'display_name' => $room->getDisplayName($userId),
                    'description' => $room->description,
                    'is_peer_to_peer' => $room->isPeerToPeer(),
                    'created_by' => $room->created_by,
                    'created_at' => $room->created_at->toDateTimeString(),
                    'updated_at' => $room->updated_at->toDateTimeString(),
                    'unread_count' => $unreadCounts[$room->id] ?? 0,
                    'members_count' => $room->users->count(),
                    'last_message' => $room->messages->first() ? [
                        'id' => $room->messages->first()->id,
                        'message' => $room->messages->first()->message,
                        'created_at' => $room->messages->first()->created_at->toDateTimeString(),
                        'user' => [
                            'id' => $room->messages->first()->user->id,
                            'name' => $room->messages->first()->user->name,
                        ],
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'chat_rooms' => $formattedRooms->values()->all(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load chat rooms.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Get a specific chat room with messages.
     */
    public function apiGetChatRoom(int $roomId): JsonResponse
    {
        try {
            $userId = Auth::id();
            $chatRoom = $this->chatService->getChatRoom($roomId, $userId);
            $messages = $this->chatService->getMessages($roomId, $userId);
            $firstUnreadMessageId = $this->chatService->getFirstUnreadMessageId($roomId, $userId);
            
            // Mark messages as read
            $this->chatService->markMessagesAsRead($roomId, $userId);

            // Format messages
            $formattedMessages = collect($messages->items())->map(function ($message) {
                if (!$message->relationLoaded('user')) {
                    $message->load('user');
                }
                
                return [
                    'id' => $message->id,
                    'chat_room_id' => $message->chat_room_id,
                    'user_id' => $message->user_id,
                    'message' => $message->message,
                    'type' => $message->type,
                    'created_at' => $message->created_at->toDateTimeString(),
                    'user' => $message->user ? [
                        'id' => $message->user->id,
                        'name' => $message->user->name,
                        'email' => $message->user->email,
                    ] : null,
                ];
            });

            // Format peers
            $formattedPeers = $chatRoom->users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            });

            return response()->json([
                'success' => true,
                'chat_room' => [
                    'id' => $chatRoom->id,
                    'name' => $chatRoom->name,
                    'display_name' => $chatRoom->getDisplayName($userId),
                    'description' => $chatRoom->description,
                    'is_peer_to_peer' => $chatRoom->isPeerToPeer(),
                    'created_by' => $chatRoom->created_by,
                    'created_at' => $chatRoom->created_at->toDateTimeString(),
                    'updated_at' => $chatRoom->updated_at->toDateTimeString(),
                ],
                'messages' => $formattedMessages->values()->all(),
                'peers' => $formattedPeers->values()->all(),
                'first_unread_message_id' => $firstUnreadMessageId,
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'per_page' => $messages->perPage(),
                    'total' => $messages->total(),
                    'has_more' => $messages->hasMorePages(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load chat room.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Create a new chat room.
     */
    public function apiCreateChatRoom(CreateChatRoomRequest $request): JsonResponse
    {
        try {
            $chatRoom = $this->chatService->createChatRoom($request->validated(), Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Chat room created successfully.',
                'chat_room' => [
                    'id' => $chatRoom->id,
                    'name' => $chatRoom->name,
                    'display_name' => $chatRoom->getDisplayName(Auth::id()),
                    'description' => $chatRoom->description,
                    'is_peer_to_peer' => $chatRoom->isPeerToPeer(),
                    'created_by' => $chatRoom->created_by,
                    'created_at' => $chatRoom->created_at->toDateTimeString(),
                    'members' => $chatRoom->users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                        ];
                    })->values()->all(),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to create chat room.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Update a chat room.
     */
    public function apiUpdateChatRoom(int $roomId): JsonResponse
    {
        try {
            $userId = Auth::id();
            $this->chatService->verifyUserAccess($roomId, $userId);
            
            $request = request();
            $chatRoom = $this->chatService->getChatRoom($roomId, $userId);
            
            $data = $request->only(['name', 'description']);
            $chatRoom->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Chat room updated successfully.',
                'chat_room' => [
                    'id' => $chatRoom->id,
                    'name' => $chatRoom->name,
                    'display_name' => $chatRoom->getDisplayName($userId),
                    'description' => $chatRoom->description,
                    'updated_at' => $chatRoom->updated_at->toDateTimeString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to update chat room.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Delete a chat room.
     */
    public function apiDeleteChatRoom(int $roomId): JsonResponse
    {
        try {
            $userId = Auth::id();
            $this->chatService->verifyUserAccess($roomId, $userId);
            
            $chatRoom = $this->chatService->getChatRoom($roomId, $userId);
            
            // Only allow creator to delete
            if ($chatRoom->created_by !== $userId) {
                return response()->json([
                    'success' => false,
                    'error' => 'Only the room creator can delete this chat room.',
                ], 403);
            }
            
            $chatRoom->delete();

            return response()->json([
                'success' => true,
                'message' => 'Chat room deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete chat room.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Get messages for a chat room.
     */
    public function apiGetMessages(int $roomId): JsonResponse
    {
        try {
            $userId = Auth::id();
            $perPage = request()->input('per_page', 50);
            $messages = $this->chatService->getMessages($roomId, $userId, $perPage);

            $formattedMessages = collect($messages->items())->map(function ($message) {
                if (!$message->relationLoaded('user')) {
                    $message->load('user');
                }
                
                return [
                    'id' => $message->id,
                    'chat_room_id' => $message->chat_room_id,
                    'user_id' => $message->user_id,
                    'message' => $message->message,
                    'type' => $message->type,
                    'created_at' => $message->created_at->toDateTimeString(),
                    'user' => $message->user ? [
                        'id' => $message->user->id,
                        'name' => $message->user->name,
                        'email' => $message->user->email,
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'messages' => $formattedMessages->values()->all(),
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'per_page' => $messages->perPage(),
                    'total' => $messages->total(),
                    'has_more' => $messages->hasMorePages(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load messages.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Send a message to a chat room.
     */
    public function apiSendMessage(SendMessageRequest $request, int $roomId): JsonResponse
    {
        try {
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
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to send message.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Get peers (users) in a chat room.
     */
    public function apiGetPeers(int $roomId): JsonResponse
    {
        try {
            $peers = $this->chatService->getPeers($roomId, Auth::id());

            $formattedPeers = $peers->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];
            });

            return response()->json([
                'success' => true,
                'peers' => $formattedPeers->values()->all(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load peers.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Mark messages as read.
     */
    public function apiMarkMessagesAsRead(int $roomId): JsonResponse
    {
        try {
            $userId = Auth::id();
            $this->chatService->markMessagesAsRead($roomId, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Messages marked as read.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark messages as read.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Get unread message counts for all chat rooms.
     */
    public function apiGetUnreadCounts(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $chatRooms = $this->chatService->getUserChatRooms($userId);
            $unreadCounts = $this->chatService->getUnreadCounts($userId, $chatRooms);

            return response()->json([
                'success' => true,
                'unread_counts' => $unreadCounts,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load unread counts.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Get P2P chat info and messages.
     */
    public function apiGetPeerToPeerChatInfo(int $userId): JsonResponse
    {
        return $this->getPeerToPeerChatInfo($userId);
    }

    /**
     * API: Send P2P message.
     */
    public function apiSendPeerToPeerMessage(int $userId): JsonResponse
    {
        return $this->sendPeerToPeerMessage($userId);
    }

    /**
     * API: Start or continue a peer-to-peer chat.
     */
    public function apiStartPeerToPeerChat(int $userId): JsonResponse
    {
        $currentUserId = Auth::id();

        try {
            $chatRoom = $this->chatService->findOrCreatePeerToPeerChat($currentUserId, $userId);
            
            $otherPeer = $chatRoom->getOtherPeer($currentUserId);

            return response()->json([
                'success' => true,
                'message' => 'Chat started successfully.',
                'chat_room' => [
                    'id' => $chatRoom->id,
                    'name' => $chatRoom->name,
                    'display_name' => $chatRoom->getDisplayName($currentUserId),
                    'is_peer_to_peer' => $chatRoom->isPeerToPeer(),
                    'created_at' => $chatRoom->created_at->toDateTimeString(),
                ],
                'other_peer' => $otherPeer ? [
                    'id' => $otherPeer->id,
                    'name' => $otherPeer->name,
                    'email' => $otherPeer->email,
                ] : null,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to start chat.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
