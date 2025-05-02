<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'status',
        'target_date',
        'note',
        'request_date',
        'start_time',
        'end_time',
        'work_time',
    ];

    //リレーション
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function breakTimeRequests()
    {
        return $this->hasMany(BreakTimeRequest::class, 'requests_id');
    }

}
