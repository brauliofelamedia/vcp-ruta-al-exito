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
        'last_inactivity_notified_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'last_active_at' => 'datetime',
            'last_inactivity_notified_at' => 'datetime',
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

    /**
     * Determine whether residence is 'usa' or 'outside'.
     */
    public static function determineResidence(?string $country, ?string $residence = null): string
    {
        if (filled($country)) {
            $cleaned = mb_strtolower(trim($country));
            $cleaned = trim(preg_replace('/[.,]+/', '', $cleaned));
            $cleaned = preg_replace('/\s+/', ' ', $cleaned);

            $usaAliases = [
                'united states',
                'united states of america',
                'usa',
                'us',
                'estados unidos',
                'estados unidos de america',
                'eeuu',
                'ee uu',
                'eua',
            ];

            if (in_array($cleaned, $usaAliases, true)) {
                return 'usa';
            }

            if (str_starts_with($cleaned, 'united states') || str_starts_with($cleaned, 'estados unidos')) {
                return 'usa';
            }

            return 'outside';
        }

        if (filled($residence)) {
            $lowerResidence = strtolower(trim($residence));
            if (in_array($lowerResidence, ['usa', 'outside'], true)) {
                return $lowerResidence;
            }
        }

        return 'usa';
    }
}
