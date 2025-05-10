<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;


class StaffController extends Controller
{


    //スタッフ一覧ページ
    public function showStaffList(Request $request)
    {
        return view('admin.staff_list');
    }

    //スタッフ別勤怠一覧ページ
    public function staffAttendanceList(Request $request)
    {
        return view('admin.staff_attendance_list');
    }

}