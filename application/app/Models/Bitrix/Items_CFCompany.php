<?php

namespace App\Models\Bitrix;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Items_CFCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'item_type_id',
        'value',
    ];
}
