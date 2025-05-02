<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'date',
        'start_time',
        'end_time',
        'total_break_time',
    ];

    //リレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimeRequests()
    {
        return $this->hasMany(BreakTimeRequest::class);
    }

     public function attendances()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

}
