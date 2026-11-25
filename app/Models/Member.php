<?php

namespace App\Models;

class Member extends SigepaModel
{
    protected $table = 'members';

    protected $casts = [
        'birth_date' => 'date:Y-m-d',
        'id_emit_date' => 'date:Y-m-d',
        'card_emit_date' => 'date:Y-m-d',
        'member_card_emit_date' => 'date:Y-m-d',
        'was_militar' => 'boolean',
        'was_member_from_another_party' => 'boolean',
        'is_active' => 'boolean',
    ];
}
