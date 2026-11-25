<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends SigepaModel
{
    protected $table = 'provinces';

    public function municipalities(): HasMany
    {
        return $this->hasMany(Municipality::class, 'province_id');
    }
}
