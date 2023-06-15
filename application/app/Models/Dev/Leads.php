<?php

namespace App\Models\Dev;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leads extends Model
{
    use HasFactory;

    protected $table = 'dev_events_leads';

    protected $fillable = [
        'event_id',
        'name',
        'link',
        'createdAt',
        'createdBy',
        'price',
        'tags',
        'contact_link',
        'date',
    ];
}
