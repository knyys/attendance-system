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
        $page = $request->query('page', 'request');
    
        $isAdmin = $user->is_admin == 1;
    
        $requests = CorrectRequest::with('attendance.user')
            ->when(!$isAdmin, function ($query) use ($user) {
                return $query->whereHas('attendance', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            })
            ->where('status', $page === 'request' ? 0 : 1)
            ->orderBy('target_date', 'asc')
            ->get();
    
        $view = $isAdmin ? 'admin.request_list' : 'user.request_list';
        return view($view, compact('requests'));
    
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
