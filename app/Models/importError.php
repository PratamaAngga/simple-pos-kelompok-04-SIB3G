<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportError extends Model
{
    protected $fillable = [
        'source_file',
        'row_number',
        'message',
    ];
}