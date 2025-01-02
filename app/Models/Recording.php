<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recording extends Model
{
    use HasFactory;

    protected $table = 'recordings';

    protected $fillable = [
        'call_sid',
        'recording_sid',
        'phone_number',
        'recording_url',
        'recording_status',
        'recording_duration',
        'recording_channels',
        'recording_source',
        'recording_price',
        'recording_price_unit',
        'recording_error_code',
        'recording_encryption_details',
        'recording_media_type',
        'recording_track',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'recording_duration' => 'integer',
        'recording_channels' => 'integer',
        'recording_price' => 'decimal:4',
        'recording_error_code' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
