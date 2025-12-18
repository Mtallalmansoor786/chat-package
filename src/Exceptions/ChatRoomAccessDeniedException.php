<?php

namespace ChatPackage\ChatPackage\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ChatRoomAccessDeniedException extends Exception
{
    /**
     * Render the exception as an HTTP response.
     */
    public function render($request): JsonResponse|Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Unauthorized access to chat room',
                'message' => $this->getMessage() ?: 'You do not have access to this chat room.',
            ], 403);
        }

        return response()->view('errors.403', [
            'message' => $this->getMessage() ?: 'You do not have access to this chat room.',
        ], 403);
    }
}

