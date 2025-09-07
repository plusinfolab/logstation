<?php

namespace PlusinfoLab\Logstation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogTag extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'logstation_tags';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * Indicates if the model's ID is auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'entry_id',
        'tag',
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
     * Get the log entry that owns the tag.
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(LogEntry::class, 'entry_id');
    }
}
