<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'work_time',
    ];

        public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function request()
    {
        return $this->hasOne(CorrectRequest::class);
    }

    public function breakTime()
    {
        return $this->hasMany(BreakTime::class, 'user_id', 'user_id') 
                    ->where('date', $this->date); 
        
    }
}
