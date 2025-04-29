<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTimeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'start_time',
        'end_time',
    ];

    //リレーション
    public function request()
    {
        return $this->belongsTo(CorrectRequest::class, 'requests_id');
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'user_id');
    }

}
