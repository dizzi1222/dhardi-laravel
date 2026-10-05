<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * One assistant session. Addressed externally by an unguessable public id so
 * the browser never has to hold a sequential database key.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $public_id
 * @property string $locale
 * @property string|null $intent
 */
class Conversation extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'public_id',
        'locale',
        'intent',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $conversation): void {
            $conversation->public_id ??= (string) Str::uuid();
        });
    }

    /**
     * @return HasMany<Message>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * @return HasMany<LlmRun>
     */
    public function runs(): HasMany
    {
        return $this->hasMany(LlmRun::class);
    }
}
