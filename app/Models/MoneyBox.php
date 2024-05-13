<?php

namespace App\Models;

use App\Http\Resources\MoneyBoxCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoneyBox extends Model
{
    protected $fillable = [
        'user_id',
        'nombre',
        'monto',
        'director_teacher_id',
    ];
    use HasFactory;

    public function spents(){
        return $this->hasMany(Spent::class,'money_boxes_id','id');
    }
    
    public function manager(){
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function director(){
        return $this->belongsTo(Teacher::class,'director_teacher_id','id');
    }

    public function recharges(){
        return $this->hasMany(Recharge::class,'money_box_id');
    }
}
