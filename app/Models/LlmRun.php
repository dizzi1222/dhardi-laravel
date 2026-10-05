<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per model attempt, written whatever the outcome.
 *
 * The table is the reason the reliability claims are checkable: reliability is
 * not a claim about how often things work, it is a measurable function of
 * outcomes over time.
 *
 * @property int $id
 * @property string $provider
 * @property string $model
 * @property int $attempt
 * @property string $outcome
 * @property int|null $http_status
 * @property int $latency_ms
 * @property int $prompt_tokens
 * @property int $completion_tokens
 * @property float $cost_usd
 * @property bool $was_cached
 * @property string|null $error_class
 */
class LlmRun extends Model
{
    use BelongsToTenant;
    use HasFactory;

    public const OUTCOME_SUCCESS = 'success';

    public const OUTCOME_ERROR = 'error';

    public const OUTCOME_BLOCKED = 'blocked';

    public const OUTCOME_CACHED = 'cached';

    protected $fillable = [
        'tenant_id',
        'conversation_id',
        'provider',
        'model',
        'attempt',
        'outcome',
        'http_status',
        'latency_ms',
        'prompt_tokens',
        'completion_tokens',
        'cost_usd',
        'was_cached',
        'error_class',
    ];

    protected function casts(): array
    {
        return [
            'attempt' => 'integer',
            'latency_ms' => 'integer',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'cost_usd' => 'float',
            'was_cached' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Conversation, static>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * @param  Builder<LlmRun>  $query
     * @return Builder<LlmRun>
     */
    public function scopeFailures(Builder $query): Builder
    {
        return $query->where('outcome', self::OUTCOME_ERROR);
    }

    /**
     * Share of attempts that produced an answer without human intervention.
     */
    public static function successRate(): float
    {
        $total = static::query()->count();

        if ($total === 0) {
            return 0.0;
        }

        return round(static::query()->where('outcome', self::OUTCOME_SUCCESS)->count() / $total, 4);
    }
}
