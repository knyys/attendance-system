<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;


class RequestController extends Controller
{


    //一般ユーザー用申請一覧
    public function index(Request $request)
    {
         return view('user.request_list');
    }
    
}