<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Commune extends SigepaModel
{
    protected $table = 'communes';

    public function neighborhoods(): HasMany
    {
        return $this->hasMany(Neighborhood::class, 'commune_id');
    }
}
