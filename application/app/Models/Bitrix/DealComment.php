<?php

namespace App\Models\Bitrix;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealComment extends Model
{
    use HasFactory;

    protected $table = 'deal_comments';

    protected $fillable = [
        'created',
        'comment_id',
        'text',
        'author_id',
        'files',
    ];
}
