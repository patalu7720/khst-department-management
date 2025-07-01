<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChipLog extends Model
{
    protected $fillable = [
        'chip_id',
        'material',
        'batch',
        'quantity',
        'created_user',
        'status'
    ];
}
