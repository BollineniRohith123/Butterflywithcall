<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ConversationStore extends Model
{
    use SoftDeletes;

    protected $table = 'conversation_stores';

    protected $fillable = [
        'phone',
        'message_sid',
        'direction',
        'message',
        'status',
        'from',
        'to',
        'delivered_at',
        'read_at'
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Create a new conversation entry
     * 
     * @param string $phone
     * @param string $message
     * @param string $direction
     * @param array $additionalData
     * @return self
     */
    public static function createConversation(
        string $phone, 
        string $message, 
        string $direction = 'inbound', 
        array $additionalData = []
    ): self {
        $conversationData = array_merge([
            'phone' => $phone,
            'message' => $message,
            'direction' => $direction,
        ], $additionalData);

        return self::create($conversationData);
    }

    /**
     * Get conversations for a specific phone number
     * 
     * @param string $phone
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getConversationsByPhone(string $phone, int $limit = 50)
    {
        return self::where('phone', $phone)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all conversations grouped by phone number
     * 
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAllConversationsGrouped(int $limit = 50)
    {
        return self::select('phone', 
                DB::raw('MAX(created_at) as last_message_at'),
                DB::raw('COUNT(*) as message_count'))
            ->groupBy('phone')
            ->orderBy('last_message_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Scope a query to only include conversations for a specific phone number
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $phone
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPhone($query, string $phone)
    {
        return $query->where('phone', $phone);
    }

    /**
     * Get the most recent conversation for a phone number
     * 
     * @param string $phone
     * @return self|null
     */
    public static function getLatestConversation(string $phone)
    {
        return self::where('phone', $phone)
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Count conversations for a specific phone number
     * 
     * @param string $phone
     * @return int
     */
    public static function countConversations(string $phone)
    {
        return self::where('phone', $phone)->count();
    }

    /**
     * Mark conversation as read
     * 
     * @return bool
     */
    public function markAsRead()
    {
        return $this->update(['read_at' => now()]);
    }

    /**
     * Mark conversation as delivered
     * 
     * @return bool
     */
    public function markAsDelivered()
    {
        return $this->update(['delivered_at' => now(), 'status' => 'delivered']);
    }

    /**
     * Update conversation status
     * 
     * @param string $status
     * @return bool
     */
    public function updateStatus(string $status)
    {
        return $this->update(['status' => $status]);
    }
}
