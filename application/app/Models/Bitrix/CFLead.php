<?php

namespace App\Models\Bitrix;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CFLead extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'isRequired',
        'isReadOnly',
        'isImmutable',
        'isMultiple',
        'isDynamic',
        'title',
        'listLabel',
        'isMultiple',
        'formLabel',
        'filterLabel',
        'settings',
    ];

    protected $table = 'c_f_leads';

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Items_CFLead::class, 'item_id', 'id');
    }
}
