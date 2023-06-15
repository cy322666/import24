<?php

namespace App\Models\Bitrix;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead_CF extends Model
{
    use HasFactory;

    protected $table = 'lead_custom_field';
}
