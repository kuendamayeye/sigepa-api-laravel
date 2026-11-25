<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Municipality extends SigepaModel
{
    protected $table = 'municipalities';

    public function communes(): HasMany
    {
        return $this->hasMany(Commune::class, 'municipality_id');
    }
}
