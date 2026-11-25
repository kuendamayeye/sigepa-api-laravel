<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    protected $table = 'api_tokens';

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Cria um token e devolve o valor em texto simples. */
    public static function issue(User $user, string $name, int $minutes): string
    {
        $plainToken = Str::random(64);

        self::create([
            'user_id' => $user->id,
            'name' => $name,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->addMinutes($minutes),
        ]);

        return $plainToken;
    }
}
