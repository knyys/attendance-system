<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTimeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_time',
        'end_time',
    ];

    //リレーション
    public function request()
    {
        return $this->belongsTo(Request::class, 'requests_id');
    }

    public function breakTime()
    {
        return $this->belongsTo(BreakTime::class);
    }

}
