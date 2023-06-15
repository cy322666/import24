<?php

namespace App\Models\Bitrix;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyComment extends Model
{
    use HasFactory;

    protected $table = 'company_comments';

    protected $fillable = [
        'created',
        'comment_id',
        'company_id',
        'text',
        'author_id',
        'files',
    ];
}
