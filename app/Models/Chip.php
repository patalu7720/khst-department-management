<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chip extends Model
{
    protected $fillable = [
        'material',
        'batch',
        'quantity',
        'created_user',
    ];
}
