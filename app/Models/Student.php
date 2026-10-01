<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'email',
        'full_name',
        'registration_status',
        'system',
        'residence',
        'current_station',
        'completed_stations',
        'completed_tasks',
        'progress_percentage',
        'ghl_contact_id',
        'metadata',
        'last_active_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'last_active_at' => 'datetime',
        ];
    }

    public function progressEntries(): HasMany
    {
        return $this->hasMany(StudentProgress::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
