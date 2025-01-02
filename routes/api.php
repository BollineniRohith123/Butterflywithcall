<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\TwilioWebhookController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\RecordingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Twilio Webhook Routes (outside v1 prefix for direct access)
Route::prefix('webhooks/twilio')->group(function () {
    Route::post('message-status', [TwilioWebhookController::class, 'messageStatus']);
    Route::post('incoming-message', [TwilioWebhookController::class, 'incomingMessage']);
});

Route::prefix('v1')->group(function () {
    // Recording routes
    Route::get('/recordings', [RecordingController::class, 'index']);
    Route::get('/recordings/{id}', [RecordingController::class, 'show']);
    Route::delete('/recordings/{id}', [RecordingController::class, 'destroy']);

    Route::prefix('leads')->group(function () {
        Route::get('test-whatsapp', [LeadController::class, 'testWhatsApp']);
        Route::get('{id}/conversations', [LeadController::class, 'getConversations']);
        Route::post('{id}/conversations', [LeadController::class, 'sendMessage']);
        Route::get('{id}/conversation-analysis', [LeadController::class, 'getConversationAnalysis']);
    });

    // Conversation routes
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::options('/conversations', function() { return response()->json(); });
    
    Route::get('/conversations/{phone}', [ConversationController::class, 'show']);
    Route::options('/conversations/{phone}', function() { return response()->json(); });
    
    Route::post('/conversations/send', [ConversationController::class, 'send']);
    Route::options('/conversations/send', function() { return response()->json(); });
    
    Route::post('/conversations/{id}/read', [ConversationController::class, 'markAsRead']);
    Route::options('/conversations/{id}/read', function() { return response()->json(); });
    
    Route::post('/conversations/{id}/status', [ConversationController::class, 'updateStatus']);
    Route::options('/conversations/{id}/status', function() { return response()->json(); });
});
