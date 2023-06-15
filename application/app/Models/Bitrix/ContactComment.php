<?php

namespace App\Models\Bitrix;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactComment extends Model
{
    use HasFactory;

    protected $table = 'contact_comments';

    protected $fillable = [
        'created',
        'comment_id',
        'contact_id',
        'text',
        'author_id',
        'files',
    ];
}
