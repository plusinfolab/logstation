<?php

namespace PlusinfoLab\Logstation\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LogEntry extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'logstation_entries';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'context' => 'array',
        'extra' => 'array',
        'level' => 'integer',
        'exception_line' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'batch_id',
        'type',
        'channel',
        'level',
        'level_name',
        'message',
        'context',
        'extra',
        'exception_class',
        'exception_message',
        'exception_trace',
        'exception_file',
        'exception_line',
        'request_method',
        'request_url',
        'request_ip',
        'request_user_agent',
        'user_id',
        'user_email',
        'session_id',
        'request_id',
        'created_at',
    ];

    /**
     * Get the database connection for the model.
     */
    public function getConnectionName()
    {
        $connection = config('logstation.database.connection');
        return $connection ?: config('database.default');
    }

    /**
     * Get the tags for the log entry.
     */
    public function tags(): HasMany
    {
        return $this->hasMany(LogTag::class, 'entry_id');
    }

    /**
     * Get the user that created the log entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    /**
     * Scope a query to only include entries of a given type.
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level_name', $level);
    }

    /**
     * Scope a query to only include entries from a given channel.
     */
    public function scopeByChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Scope a query to only include entries within a date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate = null)
    {
        $query->where('created_at', '>=', $startDate);

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope a query to search entries by message or context.
     */
    public function scopeSearch($query, $search)
    {
        $driver = $query->getConnection()->getDriverName();

        return $query->where(function ($q) use ($search, $driver) {
            $q->where('message', 'like', "%{$search}%")
                ->orWhere('exception_message', 'like', "%{$search}%");

            // Only use JSON_SEARCH for MySQL/MariaDB
            if (in_array($driver, ['mysql', 'mariadb'])) {
                $q->orWhereRaw("JSON_SEARCH(context, 'one', ?) IS NOT NULL", ["%{$search}%"]);
            } else {
                // For SQLite and PostgreSQL, use LIKE on the JSON column
                $q->orWhere('context', 'like', "%{$search}%");
            }
        });
    }

    /**
     * Scope a query to only include entries with specific tags.
     */
    public function scopeWithTag($query, $tag)
    {
        return $query->whereHas('tags', function ($q) use ($tag) {
            $q->where('tag', $tag);
        });
    }

    /**
     * Scope a query to only include entries by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include entries after a specific timestamp.
     */
    public function scopeBySince($query, $since)
    {
        return $query->where('created_at', '>=', $since);
    }

    /**
     * Get formatted context for display.
     */
    public function getFormattedContextAttribute(): ?string
    {
        if (empty($this->context)) {
            return null;
        }

        return json_encode($this->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get formatted extra data for display.
     */
    public function getFormattedExtraAttribute(): ?string
    {
        if (empty($this->extra)) {
            return null;
        }

        return json_encode($this->extra, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Check if the entry has an exception.
     */
    public function hasException(): bool
    {
        return !empty($this->exception_class);
    }

    /**
     * Get the level badge color.
     */
    public function getLevelColorAttribute(): string
    {
        return match ($this->level_name) {
            'emergency', 'alert', 'critical' => 'red',
            'error' => 'orange',
            'warning' => 'yellow',
            'notice' => 'blue',
            'info' => 'green',
            'debug' => 'gray',
            default => 'gray',
        };
    }
}
