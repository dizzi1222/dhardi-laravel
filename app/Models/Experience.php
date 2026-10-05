<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\ReadsTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A role held, with the dates and the stack that came with it.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $slug
 * @property string $role
 * @property string $organisation
 * @property string|null $employment_type
 * @property string|null $location
 * @property bool $is_remote
 * @property Carbon $start_date
 * @property Carbon|null $end_date
 * @property array<int, string>|null $stack
 */
class Experience extends Model
{
    use BelongsToTenant;
    use HasFactory;
    use ReadsTranslations;

    protected $fillable = [
        'tenant_id',
        'slug',
        'role',
        'organisation',
        'organisation_url',
        'employment_type',
        'location',
        'is_remote',
        'start_date',
        'end_date',
        'sort_order',
        'stack',
    ];

    protected function casts(): array
    {
        return [
            'is_remote' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
            'stack' => 'array',
        ];
    }

    /**
     * @return HasMany<ExperienceTranslation>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ExperienceTranslation::class);
    }

    public function isCurrent(): bool
    {
        return $this->end_date === null;
    }

    public function durationMonths(): int
    {
        $end = $this->end_date ?? now();

        // `diffInMonths` returns a signed float since Carbon 3, so it is floored
        // rather than cast: a role that lasted a day should read as one month,
        // not as zero.
        return max(1, (int) floor(abs($this->start_date->diffInMonths($end))));
    }

    protected function translationMap(): array
    {
        return $this->relationLoaded('translations')
            ? $this->translations->keyBy('locale')->all()
            : $this->translations()->get()->keyBy('locale')->all();
    }

    protected function fallbackLocale(): string
    {
        return config('tenancy.locale_fallback', 'en');
    }
}
