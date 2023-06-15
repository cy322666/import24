<?php

namespace App\Models\Bitrix;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadComment extends Model
{
    use HasFactory;

    protected $table = 'lead_comments';

    protected $fillable = [
        'created',
        'comment_id',
        'lead_id',
        'text',
        'author_id',
        'files',
    ];
}
