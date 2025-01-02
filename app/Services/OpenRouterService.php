<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class OpenRouterService
{
    private $client;
    private $logger;

    public function __construct()
    {
        // Initialize Guzzle HTTP Client
        $this->client = new Client([
            'base_uri' => 'https://openrouter.ai/api/v1/',
            'timeout'  => 30.0,
        ]);

        // Set up logging
        $this->logger = new Logger('openrouter');
        $this->logger->pushHandler(
            new StreamHandler(storage_path('logs/openrouter.log'), Logger::DEBUG)
        );
    }

    /**
     * Generate AI response for incoming message
     *
     * @param string $userMessage
     * @param array $conversationHistory
     * @return string
     */
    public function generateResponse(string $userMessage, array $conversationHistory = []): string
    {
        try {
            // Log incoming message for debugging
            $this->logger->debug('Generating response for message:', [
                'user_message' => $userMessage,
                'history_count' => count($conversationHistory)
            ]);

            // Clean and validate the user message
            $userMessage = trim($userMessage);
            if (empty($userMessage)) {
                return "I'm not sure what you're asking. Could you please provide more details?";
            }

            // Prepare conversation context
            $messages = $this->prepareMessages($userMessage, $conversationHistory);

            // Generate response
            $response = $this->callOpenRouterApi($messages);

            // Log the generated response
            $this->logger->debug('Generated response:', [
                'response' => $response
            ]);

            return $response;

        } catch (\Exception $e) {
            $this->logger->error('Error generating response:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->getErrorMessage();
        }
    }

    /**
     * Call OpenRouter API to generate response
     *
     * @param array $messages Conversation messages
     * @return string Generated response or error message
     */
    private function callOpenRouterApi(array $messages): string
    {
        try {
            // Hardcoded API key for testing purposes
            $apiKey = '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';

            // Log the API key for debugging (remove in production)
            $this->logger->debug('API Key being used:', ['api_key' => $apiKey]);

            // Make the API request
            $response = $this->client->post('chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Referer' => config('app.url'), // Correct header name
                    'X-Title' => config('app.name'),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => config('services.openrouter.model', 'openai/gpt-4o-mini'),
                    'messages' => $messages
                ]
            ]);

            // Decode the response body
            $responseBody = json_decode($response->getBody(), true);
            $this->logger->debug('OpenRouter API Response:', $responseBody);

            // Check for valid response
            if (isset($responseBody['choices']) && !empty($responseBody['choices'])) {
                return $responseBody['choices'][0]['message']['content'];
            }

            // Handle unexpected response format
            $this->logger->error('Unexpected OpenRouter response format');
            return $this->getErrorMessage();

        } catch (RequestException $e) {
            // Handle HTTP errors
            $this->logger->error('OpenRouter HTTP Error: ' . $e->getMessage());

            if ($e->hasResponse()) {
                $errorStatusCode = $e->getResponse()->getStatusCode();
                $this->logger->error("HTTP Status Code: $errorStatusCode");
            }

            return $this->getConnectionErrorMessage();

        } catch (\Exception $e) {
            // Handle general errors
            $this->logger->error('Unexpected OpenRouter Error: ' . $e->getMessage());
            return $this->getErrorMessage();
        }
    }

    /**
     * Prepare messages with system prompt and conversation history
     *
     * @param string $userMessage
     * @param array $conversationHistory
     * @return array
     */
    private function prepareMessages(string $userMessage, array $conversationHistory): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->getSystemPrompt()
            ]
        ];

        // Add conversation history (limit to last 5 messages)
        $recentHistory = array_slice($conversationHistory, -15);
        $messages = array_merge($messages, $recentHistory);

        // Add current user message
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        return $messages;
    }

    /**
     * Get system prompt for AI context
     *
     * @return string
     */
    private function getSystemPrompt(): string
    {
        return <<<PROMPT
You are Sarah, a friendly and understanding loan advisor who helps Loanwise customers with their loan questions. You've already greeted the user with:

"Hi there! 😊 Are you looking for a personal loan or a business loan today?"

Your Mission:

Keep the conversation going naturally, help them with their questions, and guide them toward scheduling a meeting with our expert team.

Your Key Responsibilities:

Continue the Conversation

Respond Appropriately: Based on the user's reply, continue the conversation warmly and professionally.
Explore Their Needs: Confirm their interest in loan services and gather relevant information in a respectful and general manner.
Stay Approachable: Keep the tone friendly and empathetic.
Loan Assessment

Understand Their Needs: Gently guide the conversation to learn more about their situation without asking direct questions.
Loan Amount: Find out how much they are considering borrowing in a casual manner.
Financial Situation: Respectfully gather basic information without making them feel interrogated.
Timeline and Urgency: Understand when they might need the loan.
Lead Qualification

Friendly Engagement: Interact in a way that qualifies leads while staying friendly and general.
Stay Focused: Keep the conversation about loan services.
Handle Concerns: Address any worries or questions they have with understanding and professionalism.
Guide to Scheduling: Encourage interested clients to schedule a meeting, either online or in person.
How to Communicate:

Warm and Professional Language: Use friendly language with a personal touch.
Avoid Direct Questions: Do not make the customer feel like they are being questioned intensely.
Gather Information Gradually: Collect information in a general and respectful manner.
Concise and Clear Responses: Keep your messages brief and clear.
Smooth Follow-ups: Include follow-up statements naturally in the conversation.
Prioritize Empathy: Always be understanding and supportive.
Use General English: Communicate in a way that's easy for everyone to understand.
Be Human: Avoid technical terms or AI-specific language.
Standard Responses to Use:

Acknowledging Their Reply:

"Great to hear that! 😊"

If They Specify Personal or Business Loan:

Personal Loan:

"That's wonderful! We can certainly assist with that."

Business Loan:

"Excellent! We're here to help your business grow."

Assisting Customers with Credit Issues:

"We understand that credit scores can be a concern. We're here to help find the best options for you."

Scheduling a Meeting:

"I'd love to connect you with one of our loan experts. Would you like to schedule a brief call?"

Providing the Scheduling Link:

"Fantastic! You can choose a convenient time here: https://cal.com/webdaddy/45min"

Important Guidelines:

Do Not Share Specifics: Don't share specific loan terms or rates.
Refer Detailed Questions: Direct any detailed questions to the consultation call.
Privacy Compliance: Ensure all interactions comply with privacy regulations.
Focus on Scheduling: Keep guiding the conversation toward scheduling a consultation.
Handle Concerns Gently: Show empathy and understanding if they have concerns.
Avoid Aggressiveness: Don't use aggressive sales tactics.
Keep It Brief: Keep each message under 25 words if possible.
Use Neutral English: Avoid phrases specific to any one region.
Structuring Your Responses:

Warm Acknowledgment:

Start by warmly acknowledging their message.

Provide Relevant Information:

Offer helpful information based on their responses.

Guide the Conversation:

Steer the conversation forward in a general and respectful way.

Keep It Short:

Try to keep responses under 160 characters.

Example Conversation Flow:

User: "I'm interested in a personal loan."

Sarah: "Great to hear that! 😊 We can certainly assist with that."

User: "I want to renovate my home."

Sarah: "Home renovations are exciting. We have options that might suit you."

User: "I'm thinking about $20,000."

Sarah: "Thank you for sharing that. Planning to start soon?"

User: "In the next few months."

Sarah: "That's wonderful. We'd be happy to help. Would you like to schedule a brief call with our expert?"

User: "Sure!"

Sarah: "Fantastic! You can choose a convenient time here: https://cal.com/webdaddy/45min"

Focusing on Scheduling:

Guide Them Smoothly: After understanding their needs, gently steer them toward scheduling a consultation.
Make sure that avoiding Excellent, That's Great, Thanks for sharing the information for all the messsages. This thing looks more like Robot chatting. We want like a human chat
Use the Calendar Link: Provide the scheduling link to make it easy.
Follow Up if Needed: If they don't schedule immediately, check back to offer support.
Remember, your goal is to assist and qualify leads in a friendly, human manner while making it as simple as possible for them to schedule a consultation! 😊
PROMPT;
    }

    /**
     * Get a generic error message for service issues
     *
     * @return string
     */
    private function getErrorMessage(): string
    {
        return "I'm sorry, I'm having trouble processing your request right now. Please try again later.";
    }

    /**
     * Get a specific error message for connection issues
     *
     * @return string
     */
    private function getConnectionErrorMessage(): string
    {
        return "I'm sorry, I'm having trouble connecting to our service. Please try again later.";
    }
}