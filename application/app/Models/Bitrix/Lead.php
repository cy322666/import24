<?php

namespace App\Models\Bitrix;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'status',
    ];

    public function comments()
    {
        return $this->hasMany(LeadComment::class, 'lead_id', 'lead_id');
    }
}
