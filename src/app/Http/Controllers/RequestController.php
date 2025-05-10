<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CorrectRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;

class RequestController extends Controller
{
    //一般ユーザー用申請一覧,
    //管理者用申請一覧
    public function showRequestList(Request $request)
    {
        $user = auth()->user();

    // クエリパラメータ 'page' が無ければ 'request' 扱い
    $page = $request->query('page', 'request');

    // 管理者の場合
    if ($user->role === 'admin') {
        $requests = CorrectRequest::with('attendance.user')
            ->where('status', $page === 'request' ? 0 : 1)
            ->orderBy('target_date', 'asc')
            ->get();

        return view('admin.request_list', compact('requests'));
    }

    // 一般ユーザーの場合
    $requests = CorrectRequest::with('attendance.user')
        ->whereHas('attendance', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('status', $page === 'request' ? 0 : 1)
        ->orderBy('target_date', 'asc')
        ->get();

    return view('user.request_list', compact('requests'));
    }

    

    //管理者用申請画面
    public function showApproveForm()
    {

    }

    //管理者用申請承認
    public function approveRequest()
    {
        
    }


}
