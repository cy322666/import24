<?php

namespace App\Models\Bitrix;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'status',
    ];

    public function comments()
    {
        return $this->hasMany(CompanyComment::class, 'company_id', 'company_id');
    }
}
