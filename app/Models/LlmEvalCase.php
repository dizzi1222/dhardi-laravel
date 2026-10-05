<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A golden case: a prompt plus the fragments a correct answer must contain.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $key
 * @property string $prompt
 * @property array<int, string>|null $expected_contains
 * @property string $locale
 * @property bool $is_active
 */
class LlmEvalCase extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'key',
        'prompt',
        'expected_contains',
        'locale',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'expected_contains' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<LlmEvalResult>
     */
    public function results(): HasMany
    {
        return $this->hasMany(LlmEvalResult::class);
    }

    public function passes(string $output): bool
    {
        foreach ((array) $this->expected_contains as $fragment) {
            if (! str_contains(mb_strtolower($output), mb_strtolower((string) $fragment))) {
                return false;
            }
        }

        return true;
    }
}
