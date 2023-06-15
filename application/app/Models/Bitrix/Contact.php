<?php

namespace App\Models\Bitrix;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'status',
    ];

    public function comments()
    {
        return $this->hasMany(ContactComment::class, 'contact_id', 'contact_id');
    }
}
