<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadConversation;
use Twilio\Rest\Client;

class TwilioService
{
    protected $client;
    protected $whatsappNumber;
    protected $messagingServiceSid;

    public function __construct()
    {
        try {
            $accountSid = config('services.twilio.account_sid');
            $authToken = config('services.twilio.auth_token');
            $this->whatsappNumber = config('services.twilio.whatsapp_number');
            $this->messagingServiceSid = config('services.twilio.messaging_service_sid');
            
            // Debug Twilio configuration
            \Log::debug('Twilio Configuration:', [
                'account_sid' => $accountSid,
                'whatsapp_number' => $this->whatsappNumber,
                'messaging_service_sid' => $this->messagingServiceSid,
                'auth_token_exists' => !empty($authToken),
                'auth_token_length' => strlen($authToken)
            ]);

            if (empty($accountSid) || empty($authToken) || empty($this->whatsappNumber)) {
                throw new \Exception('Twilio configuration is incomplete. Please check your .env file.');
            }

            $this->client = new Client($accountSid, $authToken);
        } catch (\Exception $e) {
            \Log::error('Twilio Init Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    // public function sendWelcomeMessage($phoneNumber, $campaignName)
    // {
    //     \Log::info('Sending welcome message:', [
    //         'phone' => $phoneNumber,
    //         'campaign' => $campaignName
    //     ]);
    //     $message = "Hello! 😊 I'm Sarah from LoanWise. Are you interested in a Personal Loan or Business Loan?";
    //     return $this->sendMessage($phoneNumber, $message);
    // }
    
    public function sendWelcomeMessage($phoneNumber, $campaignName)
    {
        \Log::info('Sending welcome message:', [
            'phone' => $phoneNumber,
            'campaign' => $campaignName
        ]);
    
        // Replace these with your actual template ID and variables
        $templateOptions = [
            'template_id' => config('services.twilio.welcome_template_id', 'HXcb5cde42fc163fa593305eaaab602cc7'),
            'template_data' => [
                '1' => "Hello! I'm Sarah from LoanWise. Are you interested in a Personal Loan or Business Loan?", 
                
            ]
        ];
    
        // Using empty string as message since we're using a template
        return $this->sendMessage($phoneNumber, "Hello! I'm Sarah from LoanWise. Are you interested in a Personal Loan or Business Loan?", $templateOptions);
    }

    protected function formatPhoneNumber($phoneNumber) 
    {
        // Remove any non-numeric characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        // Singapore phone number handling (8 digits, starts with 6, 8, or 9)
        if (preg_match('/^(6|8|9)\d{7}$/', $phone)) {
            $phone = '+65' . $phone;
        }
        // If number is already prefixed with +65, ensure it's a valid Singapore number
        else if (preg_match('/^\+65(6|8|9)\d{7}$/', $phone)) {
            // Already in correct format, do nothing
        }
        // If number starts with 91, log and return empty (block Indian numbers)
        else if (preg_match('/^(91|0?91|\+91)/', $phone)) {
            \Log::channel('twilio_test')->warning('Blocked Non-Singapore Number', [
                'input' => $phoneNumber,
                'sanitized' => $phone,
                'reason' => 'Not a Singapore number'
            ]);
            return ''; // Return empty to prevent sending
        }
        // If number is not a valid Singapore number, return empty
        else {
            \Log::channel('twilio_test')->warning('Invalid Phone Number Format', [
                'input' => $phoneNumber,
                'sanitized' => $phone,
                'reason' => 'Does not match Singapore number pattern'
            ]);
            return ''; // Return empty to prevent sending
        }
        
        \Log::channel('twilio_test')->debug('Singapore Phone number formatting:', [
            'input' => $phoneNumber,
            'formatted' => $phone,
            'is_valid_singapore_number' => preg_match('/^\+65(6|8|9)\d{7}$/', $phone)
        ]);
        
        return $phone;
    }

    public function sendMessage($to, $message, $options = [])
    {
        try {
            // Format the phone number
            $to = $this->formatPhoneNumber($to);
            
            // Explicitly check for Singapore numbers
            if (!preg_match('/^\+65(6|8|9)\d{7}$/', $to)) {
                \Log::warning('Attempted to send message to non-Singapore number', [
                    'phone_number' => $to
                ]);
                return [
                    'success' => false,
                    'error' => 'Only Singapore phone numbers are allowed'
                ];
            }

            // Format WhatsApp numbers
            $whatsappTo = 'whatsapp:' . $to;
            $whatsappFrom = 'whatsapp:' . $this->whatsappNumber;

            \Log::debug('Attempting to send WhatsApp message:', [
                'to' => $whatsappTo,
                'from' => $whatsappFrom,
                'message' => $message,
                'options' => $options
            ]);

            try {
                // Determine if this is a template message
                $isTemplateMessage = !empty($options['template_name']) || !empty($options['template_id']);

                // Validate messaging service SID
                if ($isTemplateMessage && empty($this->messagingServiceSid)) {
                    throw new \Exception('Messaging Service SID is not configured');
                }

                // Prepare message parameters
                $messageParams = [
                    'from' => $whatsappFrom
                ];



                
                if ($isTemplateMessage) {
                    // Use provided template details or fall back to config
                    $templateName = $options['template_name'] ?? config('services.twilio.template_name');
                    $templateId = $options['template_id'] ?? config('services.twilio.template_id');
                
                    // Prepare template variables
                    $templateVariables = !empty($options['template_data']) 
                        ? json_encode($options['template_data']) 
                        : '{}';
                
                    // Add template-specific parameters
                    $messageParams += [
                        'messagingServiceSid' => $this->messagingServiceSid,
                        'body' => $templateName,
                        'contentSid' => $templateId,
                        'contentVariables' => $templateVariables
                    ];
                }




                // Handle template message
                // if ($isTemplateMessage) {
                //     // Use provided template details or fall back to config
                //     $templateName = $options['template_name'] ?? config('services.twilio.template_name');
                //     $templateId = $options['template_id'] ?? config('services.twilio.template_id');

                //     // Prepare template variables
                //     $templateVariables = !empty($options['template_data']) 
                //         ? json_encode($options['template_data']) 
                //         : '{}';

                //     // Add template-specific parameters
                //     $messageParams += [
                //         'messagingServiceSid' => $this->messagingServiceSid,
                //         'body' => $templateName,
                //         'contentSid' => $templateId,
                //         'contentVariables' => $templateVariables
                //     ];
                // } 
                // Handle regular message
                else {
                    $messageParams += [
                        'body' => $message
                    ];
                }
                
                


                // Send the message
                $result = $this->client->messages->create(
                    $whatsappTo,
                    $messageParams
                );

                \Log::info('Message sent:', [
                    'sid' => $result->sid,
                    'status' => $result->status,
                    'type' => $isTemplateMessage ? 'template' : 'regular'
                ]);

                // Find lead by phone number
                $lead = Lead::whereRaw("LOWER(JSON_EXTRACT(lead_data, '$[*].field_value')) LIKE ?", ['%' . substr($to, -10) . '%'])->first();
                
                
                
                if ($lead) {
                    LeadConversation::create([
                        'lead_id' => $lead->id,
                        'message_sid' => $result->sid,
                        'direction' => 'outbound',
                        'message' => $isTemplateMessage 
                            ? $message  
                            : $message,
                        'status' => $result->status,
                        'from' => $whatsappFrom,
                        'to' => $whatsappTo
                    ]);
                }

                return [
                    'success' => true,
                    'sid' => $result->sid,
                    'status' => $result->status,
                    'details' => [
                        'direction' => $result->direction,
                        'date_created' => $result->dateCreated,
                        'date_sent' => $result->dateSent
                    ]
                ];

            } catch (\Twilio\Exceptions\RestException $e) {
                \Log::error('Twilio REST Exception:', [
                    'code' => $e->getCode(),
                    'status' => $e->getStatusCode(),
                    'message' => $e->getMessage(),
                    'more_info' => $e->getMoreInfo()
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            $error = [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'type' => get_class($e),
                'trace' => $e->getTraceAsString()
            ];
            
            \Log::error('Twilio WhatsApp Error:', $error);
            
            return [
                'success' => false,
                'error' => $error
            ];
        }
    }

    public function testMessage($to)
    {
        // Ensure logging is working
        \Log::channel('twilio_test')->info('Test Message Attempt', [
            'original_input' => $to,
            'timestamp' => now()->toDateTimeString(),
            'method' => 'testMessage'
        ]);

        // Log the raw input for debugging
        \Log::info('Twilio Test Raw Input', [
            'input' => $to,
            'channel' => 'twilio_test'
        ]);

        // Attempt to format the phone number
        $formattedNumber = $this->formatPhoneNumber($to);

        // If formatting returns an empty string, it means the number is invalid
        if (empty($formattedNumber)) {
            $logDetails = [
                'original_number' => $to,
                'rejection_reason' => 'Invalid phone number format',
                'expected_format' => 'Singapore number starting with 6, 8, or 9 and 8 digits',
                'input_length' => strlen($to),
                'input_starts_with' => substr($to, 0, 2)
            ];
            
            \Log::channel('twilio_test')->warning('Message Sending Blocked', $logDetails);
            
            return [
                'success' => false,
                'error' => 'Only Singapore phone numbers are allowed',
                'details' => $logDetails
            ];
        }

        // Original test message logic
        $message = "Hello! This is a test message from Leads Pro. Your WhatsApp number is: " . $to;
        $result = $this->sendMessage($formattedNumber, $message);
        
        \Log::channel('twilio_test')->debug('Test Message Result', [
            'phone_number' => $to,
            'formatted_number' => $formattedNumber,
            'result' => $result
        ]);
        
        return $result;
    }

    /**
     * Public method to test phone number formatting
     * 
     * @param string $phoneNumber
     * @return string
     */
    public function testFormatPhoneNumber($phoneNumber) 
    {
        return $this->formatPhoneNumber($phoneNumber);
    }

    /**
     * Send a message using a Twilio template
     * 
     * @param string $to Phone number to send the message to
     * @param string $templateId Twilio template ID
     * @param array $templateData Optional template data for dynamic content
     * @return array Message sending result
     */
    public function sendTemplateMessage($to, $templateId, $templateData = [])
    {
        return $this->sendMessage($to, '', [
            'template_id' => $templateId,
            'template_data' => $templateData
        ]);
    }
    
    
}
