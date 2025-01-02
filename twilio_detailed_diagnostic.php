<?php
require __DIR__ . '/vendor/autoload.php';

use Twilio\Rest\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Create a new logger
$logger = new Logger('twilio_detailed_diagnostic');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
$logger->pushHandler(new StreamHandler(storage_path('logs/twilio_detailed_diagnostic.log'), Logger::DEBUG));

try {
    // Retrieve Twilio credentials from config
    $accountSid = config('services.twilio.account_sid');
    $authToken = config('services.twilio.auth_token');
    $whatsappNumber = config('services.twilio.whatsapp_number');
    $messagingServiceSid = config('services.twilio.messaging_service_sid');

    $logger->info('Detailed Twilio Configuration Check', [
        'account_sid' => $accountSid,
        'whatsapp_number' => $whatsappNumber,
        'messaging_service_sid' => $messagingServiceSid
    ]);

    // Initialize Twilio Client
    $client = new Client($accountSid, $authToken);

    // Verify Messaging Service
    $logger->info('Attempting to Fetch Messaging Service Details');
    try {
        $messagingService = $client->messaging->v1->services($messagingServiceSid)->fetch();
        $logger->info('Messaging Service Details', [
            'sid' => $messagingService->sid,
            'friendly_name' => $messagingService->friendlyName,
            'type' => $messagingService->type
        ]);
    } catch (\Exception $serviceError) {
        $logger->error('Messaging Service Fetch Failed', [
            'error' => $serviceError->getMessage(),
            'service_sid' => $messagingServiceSid
        ]);
    }

    // Attempt to list templates
    $logger->info('Attempting to List WhatsApp Templates');
    try {
        // Note: The method to list templates might vary depending on Twilio API version
        $templates = $client->messaging->v1->services($messagingServiceSid)->templates->read();
        
        if (count($templates) > 0) {
            $logger->info('WhatsApp Templates Found:', [
                'template_count' => count($templates)
            ]);
            
            foreach ($templates as $template) {
                $logger->info('Template Details', [
                    'sid' => $template->sid,
                    'friendly_name' => $template->friendlyName
                ]);
            }
        } else {
            $logger->warning('No templates found in the messaging service');
        }
    } catch (\Exception $templateError) {
        $logger->error('Template Retrieval Failed', [
            'error' => $templateError->getMessage(),
            'details' => [
                'code' => $templateError->getCode(),
                'message' => $templateError->getMessage()
            ]
        ]);
    }

    // Test sending a message
    $logger->info('Attempting to Send Test WhatsApp Message');
    try {
        $testPhoneNumber = '+6591878478';
        $whatsappTo = 'whatsapp:' . $testPhoneNumber;
        $whatsappFrom = 'whatsapp:' . $whatsappNumber;

        $result = $client->messages->create(
            $whatsappTo,
            [
                'from' => $whatsappFrom,
                'messagingServiceSid' => $messagingServiceSid,
                'body' => 'whatsapp_bot',
                'contentVariables' => json_encode([
                    'name' => 'Test User',
                    'amount' => '1000'
                ])
            ]
        );

        $logger->info('Test Message Sent Successfully', [
            'sid' => $result->sid,
            'status' => $result->status
        ]);
    } catch (\Exception $messageError) {
        $logger->error('Message Sending Failed', [
            'error' => $messageError->getMessage(),
            'details' => [
                'code' => $messageError->getCode(),
                'message' => $messageError->getMessage()
            ]
        ]);
    }

} catch (\Exception $e) {
    $logger->error('Twilio Diagnostic Failed', [
        'error' => $e->getMessage(),
        'code' => $e->getCode(),
        'trace' => $e->getTraceAsString()
    ]);

    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
