<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConversationStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConversationController extends Controller
{
    /**
     * Get all conversations grouped by phone
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $conversations = ConversationStore::getAllConversationsGrouped();
            
            // Get the latest message for each phone number
            $conversationsWithDetails = $conversations->map(function ($conversation) {
                $latest = ConversationStore::getLatestConversation($conversation->phone);
                return [
                    'phone' => $conversation->phone,
                    'from' => $latest->from,
                    'to' => $latest->to,
                    'message' => $latest->message,
                    'last_message_at' => $conversation->last_message_at,
                    'message_count' => $conversation->message_count
                ];
            });

            return response()->json($conversationsWithDetails);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch conversations'], 500);
        }
    }

    /**
     * Get messages for a specific phone number
     *
     * @param string $phone
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($phone)
    {
        try {
            $messages = ConversationStore::getConversationsByPhone($phone);
            return response()->json($messages);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch messages'], 500);
        }
    }

    /**
     * Send a new message
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'message' => 'required|string',
            'direction' => 'required|in:inbound,outbound'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $conversation = ConversationStore::createConversation(
                $request->phone,
                $request->message,
                $request->direction,
                [
                    'from' => $request->direction === 'outbound' ? 'Support' : 'Customer',
                    'to' => $request->direction === 'outbound' ? 'Customer' : 'Support',
                    'status' => 'sent'
                ]
            );

            if ($request->direction === 'outbound') {
                $conversation->markAsDelivered();
            }

            return response()->json($conversation);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send message'], 500);
        }
    }

    /**
     * Mark a message as read
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($id)
    {
        try {
            $conversation = ConversationStore::findOrFail($id);
            $conversation->markAsRead();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to mark message as read'], 500);
        }
    }

    /**
     * Update message status
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $conversation = ConversationStore::findOrFail($id);
            $conversation->updateStatus($request->status);
            return response()->json($conversation);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update message status'], 500);
        }
    }
}