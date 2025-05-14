<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StaffController;



/******* 一般ユーザー用 *******/

//ログイン
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

//勤怠画面
Route::get('/attendance', [AttendanceController::class, 'create']); //登録画面表示
Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.action'); //登録
Route::get('/attendance/list', [AttendanceController::class, 'showAttendanceList'])->name('attendance.list'); //一覧
Route::get('/attendance/{id}', [AttendanceController::class, 'AttendanceDetail'])->name('attendance.detail');//詳細
Route::post('/attendance/{id}', [AttendanceController::class, 'editAttendanceDetail'])->name('detail.request');//修正申請







/******* 管理者用 *******/
Route::prefix('admin')->group(function () {
    Route::get('/login', function () {
        return view('auth.admin_login'); 
    });
    Route::post('/login', [LoginController::class, 'adminLogin']);
    Route::post('/logout', [LoginController::class, 'adminLogout'])->name('adminLogout');

    Route::get('/attendance/list', [AttendanceController::class, 'showAdminAttendanceList'])->name('admin.attendance.list'); //一覧
    Route::get('/attendance/{id}', [AttendanceController::class, 'adminAttendanceDetail'])->name('admin.attendance.detail'); //詳細
    Route::get('/staff/list', [StaffController::class, 'showStaffList'])->name('admin.staff.list'); //スタッフ一覧
    Route::get('/attendance/staff/{id}', [StaffController::class, 'staffAttendanceList'])->name('admin.staff.attendance.list'); //スタッフ別勤怠一覧
});


Route::get('/request/approve/{attendance_correct_request}', [RequestController::class, 'showApproveForm'])->name('approve.form'); //申請承認画面
Route::patch('/request/approve/{attendance_correct_request}', [RequestController::class, 'approveRequest'])->name('request.approve'); //申請承認



/****** ミドルウェアでユーザーか管理者か区別 ******/
Route::middleware(['auth'])->get('/stamp_correction_request/list', [RequestController::class, 'showRequestList'])
    ->name('request.list'); //申請一覧
