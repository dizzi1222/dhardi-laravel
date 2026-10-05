<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\ReadsTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A shipped piece of work. Carries the metrics that make the claim checkable.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $slug
 * @property string $name
 * @property string|null $role
 * @property string|null $repo_url
 * @property string|null $live_url
 * @property int $year
 * @property string $status
 * @property bool $is_featured
 * @property bool $is_public_repo
 * @property array<int, string>|null $stack
 * @property array<string, string|int>|null $metrics
 */
class Project extends Model
{
    use BelongsToTenant;
    use HasFactory;
    use ReadsTranslations;

    protected $fillable = [
        'tenant_id',
        'slug',
        'name',
        'role',
        'repo_url',
        'live_url',
        'case_study_url',
        'year',
        'status',
        'is_featured',
        'is_public_repo',
        'sort_order',
        'stack',
        'metrics',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_public_repo' => 'boolean',
            'stack' => 'array',
            'metrics' => 'array',
        ];
    }

    /**
     * @return HasMany<ProjectTranslation>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ProjectTranslation::class);
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
