<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A customer of the deployment. Isolated by having its own instance.
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $default_locale
 * @property string|null $brand_name
 * @property string $brand_accent
 * @property string $plan
 * @property bool $is_active
 * @property array<string, mixed>|null $meta
 */
class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'default_locale',
        'brand_name',
        'brand_accent',
        'plan',
        'is_active',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'meta' => 'array',
        ];
    }

    /**
     * Content and telemetry owned by this customer.
     *
     * Declared here so provisioning can report what it created without reaching
     * for raw queries, and so the relationship direction stays in one place.
     *
     * @return HasMany<Experience, $this>
     */
    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    /**
     * @return HasMany<Project, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * @return HasMany<Skill, $this>
     */
    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }

    /**
     * @return HasMany<Certification, $this>
     */
    public function certifications(): HasMany
    {
        return $this->hasMany(Certification::class);
    }

    /**
     * @return HasMany<LlmEvalCase, $this>
     */
    public function evalCases(): HasMany
    {
        return $this->hasMany(LlmEvalCase::class);
    }

    /**
     * @return HasMany<Conversation, $this>
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
