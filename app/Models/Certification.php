<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A credential, either completed or explicitly marked as in progress.
 *
 * In-progress certifications are kept visible rather than hidden: stating the
 * current state is more credible than listing only finished items.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string|null $issuer
 * @property string $status
 * @property int|null $issued_year
 * @property array<string, string>|null $notes
 */
class Certification extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'issuer',
        'url',
        'status',
        'issued_year',
        'notes',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'notes' => 'array',
        ];
    }

    /**
     * The note in the active language, falling back rather than going blank.
     */
    public function note(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $notes = (array) $this->notes;

        return $notes[$locale]
            ?? $notes[config('tenancy.locale_fallback', 'en')]
            ?? null;
    }
}
