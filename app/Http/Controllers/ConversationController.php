<?php

namespace App\Http\Controllers;

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
    }

    /**
     * Get messages for a specific phone number
     *
     * @param string $phone
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($phone)
    {
        $messages = ConversationStore::getConversationsByPhone($phone);
        return response()->json($messages);
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
    }

    /**
     * Mark a message as read
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($id)
    {
        $conversation = ConversationStore::findOrFail($id);
        $conversation->markAsRead();
        return response()->json(['success' => true]);
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

        $conversation = ConversationStore::findOrFail($id);
        $conversation->updateStatus($request->status);
        return response()->json($conversation);
    }
}
