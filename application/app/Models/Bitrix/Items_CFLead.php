<?php

namespace App\Models\Bitrix;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Items_CFLead extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'item_type_id',
        'value',
    ];

    protected $table = 'items_c_f_leads';
}
