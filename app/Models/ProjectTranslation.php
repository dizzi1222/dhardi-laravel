<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $project_id
 * @property string $locale
 * @property string|null $name
 * @property string $summary
 * @property string|null $problem
 * @property string|null $approach
 * @property array<int, string>|null $highlights
 */
class ProjectTranslation extends Model
{
    protected $fillable = [
        'project_id',
        'locale',
        'name',
        'summary',
        'problem',
        'approach',
        'highlights',
    ];

    protected function casts(): array
    {
        return [
            'highlights' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Project, static>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
