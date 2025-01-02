<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConversationStore;
use Carbon\Carbon;

class ConversationStoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $conversations = [
            [
                'phone' => '+919876543210',
                'messages' => [
                    [
                        'direction' => 'inbound',
                        'message' => 'Hi, I need information about your services',
                        'from' => 'Customer',
                        'to' => 'Support',
                        'status' => 'delivered',
                        'delivered_at' => Carbon::now()->subMinutes(30),
                        'read_at' => Carbon::now()->subMinutes(25),
                    ],
                    [
                        'direction' => 'outbound',
                        'message' => 'Hello! Thank you for reaching out. I\'d be happy to help. What specific services are you interested in?',
                        'from' => 'Support',
                        'to' => 'Customer',
                        'status' => 'delivered',
                        'delivered_at' => Carbon::now()->subMinutes(28),
                        'read_at' => Carbon::now()->subMinutes(27),
                    ],
                    [
                        'direction' => 'inbound',
                        'message' => 'I\'m looking for digital marketing services',
                        'from' => 'Customer',
                        'to' => 'Support',
                        'status' => 'delivered',
                        'delivered_at' => Carbon::now()->subMinutes(25),
                        'read_at' => Carbon::now()->subMinutes(24),
                    ]
                ]
            ],
            [
                'phone' => '+919988776655',
                'messages' => [
                    [
                        'direction' => 'inbound',
                        'message' => 'Hello, is this support?',
                        'from' => 'Customer',
                        'to' => 'Support',
                        'status' => 'delivered',
                        'delivered_at' => Carbon::now()->subHours(2),
                        'read_at' => Carbon::now()->subHours(1),
                    ],
                    [
                        'direction' => 'outbound',
                        'message' => 'Yes, this is support. How can I assist you today?',
                        'from' => 'Support',
                        'to' => 'Customer',
                        'status' => 'delivered',
                        'delivered_at' => Carbon::now()->subHours(1)->subMinutes(55),
                        'read_at' => Carbon::now()->subHours(1)->subMinutes(54),
                    ],
                    [
                        'direction' => 'inbound',
                        'message' => 'I need help with my account',
                        'from' => 'Customer',
                        'to' => 'Support',
                        'status' => 'delivered',
                        'delivered_at' => Carbon::now()->subHours(1)->subMinutes(50),
                        'read_at' => null,
                    ]
                ]
            ]
        ];

        foreach ($conversations as $conversation) {
            foreach ($conversation['messages'] as $message) {
                ConversationStore::create(array_merge(
                    $message,
                    ['phone' => $conversation['phone']],
                    ['message_sid' => 'SM' . uniqid()]
                ));
            }
        }
    }
}
