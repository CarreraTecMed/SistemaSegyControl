<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interested extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombreCompleto',
        'money_boxes_id'
    ];
}
