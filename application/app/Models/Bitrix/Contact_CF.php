<?php

namespace App\Models\Bitrix;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact_CF extends Model
{
    use HasFactory;

    protected $table = 'contact_custom_field';
}
