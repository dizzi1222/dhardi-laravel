<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A technology, paired with the artefact that proves depth. The `proof` field
 * is what separates a list of keywords from a claim.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $group
 * @property string $name
 * @property string|null $proof
 */
class Skill extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'group',
        'name',
        'proof',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }
}
