<?php
require __DIR__ . '/vendor/autoload.php';

use App\Services\TwilioService;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Create a new logger
$logger = new Logger('twilio_test');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
$logger->pushHandler(new StreamHandler(storage_path('logs/twilio_debug.log'), Logger::DEBUG));

// Twilio Messaging Test
try {
    // Initialize Twilio Service
    $twilioService = new TwilioService();

    // Test phone number (provided by user)
    $testPhoneNumber = '+6591878478';

    // Validate configuration
    $config = config('services.twilio');
    $logger->info('Twilio Configuration', [
        'account_sid' => $config['account_sid'] ? 'PRESENT' : 'MISSING',
        'auth_token' => $config['auth_token'] ? 'MASKED' : 'MISSING',
        'whatsapp_number' => $config['whatsapp_number'],
        'messaging_service_sid' => $config['messaging_service_sid'],
        'template_name' => $config['template_name'],
        'template_id' => $config['template_id']
    ]);

    // Debug: Validate phone number formatting
    $logger->info('Phone Number Validation', [
        'original_number' => $testPhoneNumber,
        'formatted_number' => $twilioService->testFormatPhoneNumber($testPhoneNumber)
    ]);

    // Test WhatsApp Template Message
    $logger->info('Testing WhatsApp Template Message...');
    $templateResult = $twilioService->sendMessage(
        $testPhoneNumber, 
        '', 
        [
            'template_name' => $config['template_name'], // Use template name from config
            'template_id' => $config['template_id'],
            'template_data' => [
                'name' => 'Test User',
                'amount' => '1000'
            ]
        ]
    );
    $logger->info('WhatsApp Template Message Result', [
        'success' => $templateResult['success'] ?? false,
        'details' => $templateResult
    ]);

} catch (\Exception $e) {
    $logger->error('Twilio Messaging Test Failed', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
