<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTimeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'correct_request_id',
        'start_time',
        'end_time',
        'total_break_time',
        'break_time_id' ,
    ];

    //リレーション
    public function correctRequest()
    {
        return $this->belongsTo(CorrectRequest::class, 'requests_id');
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'user_id');
    }

}
