<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
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
        return $this->hasOne(Request::class);
    }

}
