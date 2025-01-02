<?php

namespace App\Http\Controllers;

use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TwilioTestController extends Controller
{
    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    public function testTwilioMessage(Request $request)
    {
        // Default to a Singapore number if no number is provided
        $phoneNumber = $request->input('phone', '91878478');
        
        \Log::channel('twilio_test')->info('Twilio Test Message Initiated', [
            'phone_number' => $phoneNumber,
            'timestamp' => now()
        ]);

        try {
            $result = $this->twilioService->testMessage($phoneNumber);
            
            \Log::channel('twilio_test')->info('Twilio Test Message Processed', [
                'phone_number' => $phoneNumber,
                'result' => $result
            ]);

            return response()->json($result);
        } catch (\Exception $e) {
            \Log::channel('twilio_test')->error('Twilio Test Message Failed', [
                'phone_number' => $phoneNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
