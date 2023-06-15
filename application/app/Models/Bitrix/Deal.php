<?php

namespace App\Models\Bitrix;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'deal_id',
        'status',
    ];

    public function fields()
    {
        return $this->hasMany(Deal_CF::class, 'deal_id', 'deal_id');
    }

    public function comments()
    {
        return $this->hasMany(DealComment::class, 'deal_id', 'deal_id');
    }
}
