<?php

namespace PlusinfoLab\Logstation\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogSnippet extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'logstation_snippets';

    /**
     * The primary key for the model.
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'filters' => 'array',
        'is_public' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'filters',
        'user_id',
        'is_public',
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
     * Get the user that owns the snippet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    /**
     * Scope a query to only include public snippets.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope a query to only include snippets owned by a user.
     */
    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to include snippets accessible to a user.
     */
    public function scopeAccessibleBy($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere('is_public', true);
        });
    }
}
