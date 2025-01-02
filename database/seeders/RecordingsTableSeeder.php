<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Recording;
use Carbon\Carbon;

class RecordingsTableSeeder extends Seeder
{
    public function run()
    {
        // Clear existing records
        Recording::truncate();

        $recordings = [
            [
                'call_sid' => 'CA9876543210fedcba1',
                'recording_sid' => 'RE9876543210fedcba1',
                'phone_number' => '+919876543210',
                'recording_url' => 'https://api.twilio.com/2010-04-01/Recordings/RE9876543210fedcba1',
                'recording_status' => 'completed',
                'recording_duration' => 180, // 3 minutes
                'recording_channels' => 1,
                'recording_source' => 'DialVerb',
                'recording_price' => '0.0025',
                'recording_price_unit' => 'USD',
                'recording_media_type' => 'audio/x-wav',
                'created_at' => Carbon::now()->subHours(2),
                'updated_at' => Carbon::now()->subHours(2),
            ],
            [
                'call_sid' => 'CA9876543210fedcba2',
                'recording_sid' => 'RE9876543210fedcba2',
                'phone_number' => '+919876543211',
                'recording_url' => 'https://api.twilio.com/2010-04-01/Recordings/RE9876543210fedcba2',
                'recording_status' => 'processing',
                'recording_duration' => 120, // 2 minutes
                'recording_channels' => 1,
                'recording_source' => 'RecordVerb',
                'recording_price' => '0.0020',
                'recording_price_unit' => 'USD',
                'recording_media_type' => 'audio/x-wav',
                'created_at' => Carbon::now()->subHours(1),
                'updated_at' => Carbon::now()->subHours(1),
            ],
            [
                'call_sid' => 'CA9876543210fedcba3',
                'recording_sid' => 'RE9876543210fedcba3',
                'phone_number' => '+919876543212',
                'recording_url' => null,
                'recording_status' => 'failed',
                'recording_duration' => 0,
                'recording_channels' => 1,
                'recording_source' => 'DialVerb',
                'recording_price' => '0.0000',
                'recording_price_unit' => 'USD',
                'recording_error_code' => 54321,
                'recording_media_type' => 'audio/x-wav',
                'created_at' => Carbon::now()->subMinutes(30),
                'updated_at' => Carbon::now()->subMinutes(30),
            ],
            [
                'call_sid' => 'CA9876543210fedcba4',
                'recording_sid' => 'RE9876543210fedcba4',
                'phone_number' => '+919876543213',
                'recording_url' => 'https://api.twilio.com/2010-04-01/Recordings/RE9876543210fedcba4',
                'recording_status' => 'completed',
                'recording_duration' => 300, // 5 minutes
                'recording_channels' => 2,
                'recording_source' => 'RecordVerb',
                'recording_price' => '0.0040',
                'recording_price_unit' => 'USD',
                'recording_media_type' => 'audio/x-wav',
                'created_at' => Carbon::now()->subMinutes(15),
                'updated_at' => Carbon::now()->subMinutes(15),
            ],
            [
                'call_sid' => 'CA9876543210fedcba5',
                'recording_sid' => 'RE9876543210fedcba5',
                'phone_number' => '+919876543214',
                'recording_url' => 'https://api.twilio.com/2010-04-01/Recordings/RE9876543210fedcba5',
                'recording_status' => 'in-progress',
                'recording_duration' => 60, // 1 minute
                'recording_channels' => 1,
                'recording_source' => 'DialVerb',
                'recording_price' => '0.0010',
                'recording_price_unit' => 'USD',
                'recording_media_type' => 'audio/x-wav',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            // Add more recent recordings
            [
                'call_sid' => 'CA9876543210fedcba6',
                'recording_sid' => 'RE9876543210fedcba6',
                'phone_number' => '+919876543215',
                'recording_url' => 'https://api.twilio.com/2010-04-01/Recordings/RE9876543210fedcba6',
                'recording_status' => 'completed',
                'recording_duration' => 240, // 4 minutes
                'recording_channels' => 1,
                'recording_source' => 'DialVerb',
                'recording_price' => '0.0030',
                'recording_price_unit' => 'USD',
                'recording_media_type' => 'audio/x-wav',
                'created_at' => Carbon::now()->subMinutes(5),
                'updated_at' => Carbon::now()->subMinutes(5),
            ],
            [
                'call_sid' => 'CA9876543210fedcba7',
                'recording_sid' => 'RE9876543210fedcba7',
                'phone_number' => '+919876543216',
                'recording_url' => 'https://api.twilio.com/2010-04-01/Recordings/RE9876543210fedcba7',
                'recording_status' => 'processing',
                'recording_duration' => 90, // 1.5 minutes
                'recording_channels' => 1,
                'recording_source' => 'RecordVerb',
                'recording_price' => '0.0015',
                'recording_price_unit' => 'USD',
                'recording_media_type' => 'audio/x-wav',
                'created_at' => Carbon::now()->subMinutes(2),
                'updated_at' => Carbon::now()->subMinutes(2),
            ],
        ];

        foreach ($recordings as $recording) {
            Recording::create($recording);
        }
    }
}
