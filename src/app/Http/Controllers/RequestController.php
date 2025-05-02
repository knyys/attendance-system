<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CorrectRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;

class RequestController extends Controller
{
    //一般ユーザー用申請一覧
    public function showRequestList(Request $request)
    {
        $user = auth()->user();
        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);
        $date = Carbon::createFromDate($year, $month, 1)->startOfMonth();

        $page = $request->query('page', 'request');

        $requests = CorrectRequest::with('attendance.user')
            ->whereHas('attendance', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('status', $page === 'request' ? 0 : 1)
            ->whereYear('target_date', $date->year)
            ->whereMonth('target_date', $date->month)
            ->orderBy('target_date', 'asc')
            ->get();
            
        return view('user.request_list', compact('requests', 'year', 'month'));
    }
}
