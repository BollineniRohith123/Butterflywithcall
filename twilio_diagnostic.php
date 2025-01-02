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
$logger = new Logger('twilio_diagnostic');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
$logger->pushHandler(new StreamHandler(storage_path('logs/twilio_diagnostic.log'), Logger::DEBUG));

try {
    // Retrieve Twilio credentials from config
    $accountSid = config('services.twilio.account_sid');
    $authToken = config('services.twilio.auth_token');
    $whatsappNumber = config('services.twilio.whatsapp_number');
    $messagingServiceSid = config('services.twilio.messaging_service_sid');

    $logger->info('Twilio Configuration Check', [
        'account_sid' => $accountSid ? 'PRESENT' : 'MISSING',
        'auth_token' => $authToken ? 'MASKED' : 'MISSING',
        'whatsapp_number' => $whatsappNumber,
        'messaging_service_sid' => $messagingServiceSid
    ]);

    // Initialize Twilio Client
    $client = new Client($accountSid, $authToken);

    // Verify Account Information
    $logger->info('Account Information', [
        'account_sid' => $client->getAccountSid()
    ]);

    // List Messaging Services
    $logger->info('Messaging Services:');
    $messagingServices = $client->messaging->v1->services->read();
    foreach ($messagingServices as $service) {
        $logger->info('Messaging Service', [
            'sid' => $service->sid,
            'friendly_name' => $service->friendlyName
        ]);
    }

    // Validate Specific Messaging Service
    try {
        $messagingService = $client->messaging->v1->services($messagingServiceSid)->fetch();
        $logger->info('Messaging Service Details', [
            'sid' => $messagingService->sid,
            'friendly_name' => $messagingService->friendlyName
        ]);
    } catch (\Exception $serviceError) {
        $logger->error('Messaging Service Validation Failed', [
            'error' => $serviceError->getMessage(),
            'service_sid' => $messagingServiceSid
        ]);
    }

    // List WhatsApp Templates
    $logger->info('WhatsApp Message Templates:');
    try {
        $templates = $client->messaging->v1->services($messagingServiceSid)->templates->read();
        foreach ($templates as $template) {
            $logger->info('WhatsApp Template', [
                'sid' => $template->sid,
                'friendly_name' => $template->friendlyName
            ]);
        }
    } catch (\Exception $templateError) {
        $logger->error('Template Retrieval Failed', [
            'error' => $templateError->getMessage()
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
