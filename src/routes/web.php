<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StaffController;



/******* 一般ユーザー用 *******/
Route::post('login', [LoginController::class, 'login']); //ログイン
Route::post('logout', [LoginController::class, 'logout'])->name('logout'); //ログアウト
Route::get('/attendance', [AttendanceController::class, 'create']); //出勤登録ページ
Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.action'); //出勤登録処理
Route::get('/attendance/list', [AttendanceController::class, 'showAttendanceList'])->name('attendance.list'); //勤怠一覧ページ
Route::get('/attendance/{id}', [AttendanceController::class, 'AttendanceDetail'])->name('attendance.detail');//勤怠詳細ページ
Route::post('/attendance/{id}', [AttendanceController::class, 'editAttendanceDetail'])->name('detail.request');//修正申請


/******* 管理者用 *******/
Route::prefix('admin')->group(function () {
    Route::get('/login', function () {
        return view('auth.admin_login');
    }); //ログインページ
    Route::post('/login', [LoginController::class, 'adminLogin']); //ログイン
    Route::post('/logout', [LoginController::class, 'adminLogout'])->name('adminLogout'); //ログアウト
    Route::get('/attendance/list', [AttendanceController::class, 'showAdminAttendanceList'])->name('admin.attendance.list'); //勤怠一覧
    Route::get('/attendance/{id}', [AttendanceController::class, 'adminAttendanceDetail'])->name('admin.attendance.detail'); //勤怠詳細
    Route::post('/attendance/{id}', [AttendanceController::class, 'editAdminAttendanceDetail'])->name('admin.detail.request'); //修正申請
    Route::get('/staff/list', [StaffController::class, 'showStaffList'])->name('admin.staff.list'); //スタッフ一覧
    Route::get('/attendance/staff/{id}', [StaffController::class, 'staffAttendanceList'])->name('admin.staff.attendance.list'); //スタッフ別勤怠一覧
});
Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [RequestController::class, 'showApproveForm'])->name('approve.form'); //申請承認画面
Route::post('/stamp_correction_request/approve/{attendance_correct_request}', [RequestController::class, 'approveRequest'])->name('request.approve'); //申請承認



/****** ミドルウェアでユーザーか管理者か区別 ******/
//申請一覧ページ
Route::middleware(['auth'])->get('/stamp_correction_request/list', [RequestController::class, 'showRequestList'])
    ->name('request.list'); 
