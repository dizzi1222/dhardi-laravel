<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $experience_id
 * @property string $locale
 * @property string $summary
 * @property array<int, string>|null $highlights
 */
class ExperienceTranslation extends Model
{
    protected $fillable = [
        'experience_id',
        'locale',
        'summary',
        'highlights',
    ];

    protected function casts(): array
    {
        return [
            'highlights' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Experience, static>
     */
    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }
}
