<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The measured result of replaying one golden case against one model.
 *
 * @property int $id
 * @property int $llm_eval_case_id
 * @property string $provider
 * @property string $model
 * @property bool $passed
 * @property int $latency_ms
 * @property string|null $output
 */
class LlmEvalResult extends Model
{
    protected $fillable = [
        'llm_eval_case_id',
        'provider',
        'model',
        'passed',
        'latency_ms',
        'output',
    ];

    protected function casts(): array
    {
        return [
            'passed' => 'boolean',
            'latency_ms' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<LlmEvalCase, static>
     */
    public function evalCase(): BelongsTo
    {
        return $this->belongsTo(LlmEvalCase::class);
    }
}
