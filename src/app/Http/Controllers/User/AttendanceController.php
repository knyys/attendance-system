<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Carbon\Carbon;



class AttendanceController extends Controller
{
    //一般ユーザー用出勤登録ページ
    public function create(Request $request)
    {
        $now = Carbon::now();
        $now_day = $now->format('Y年n月d日');
        $dayName = $now->shortDayName;
        $now_time = $now->format('H:i');
        return view('user.attendance_register', compact('now_day', 'now_time', 'dayName'));
    }

    //一般ユーザー用出勤登録処理
    public function store(Request $request)
    {
        
    }

    //一般ユーザー用勤怠一覧ページ
    public function index(Request $request)
    {
        return view('user.attendance_list');
    }

    //一般ユーザー用勤怠詳細ページ
    public function detail(Request $request)
    {
        return view('user.attendance_detail');
    }

    //一般ユーザー用勤怠詳細処理
    public function edit(Request $request)
    {
        
    }

}