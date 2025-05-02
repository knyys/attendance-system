<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;

use App\Http\Controllers\RequestController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\AttendanceController;



/* 一般ユーザー用 */
//会員登録
//Route::get('register', [RegisterController::class, 'create']);

//ログイン
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

//勤怠画面
Route::get('/attendance', [AttendanceController::class, 'create']); //登録画面表示
Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.action'); //登録
Route::get('/attendance/list', [AttendanceController::class, 'showAttendanceList'])->name('attendance.list'); //一覧
Route::get('/attendance/{id}', [AttendanceController::class, 'AttendanceDetail'])->name('attendance.detail');//詳細
Route::post('/attendance/{id}', [AttendanceController::class, 'editAttendanceDetail'])->name('detail.request');//修正申請
Route::get('/stamp_correction_request/list', [RequestController::class, 'showRequestList'])->name('request.list'); //申請一覧



/* 管理者用 */
Route::prefix('admin')->group(function () {

    Route::get('/login', function () {
        return view('auth.login'); 
    });
    Route::post('/login', [LoginController::class, 'adminLogin']);
    Route::post('/logout', [LoginController::class, 'adminLogout'])->name('adminLogout');

    Route::get('/attendance/list', [AttendanceController::class, 'showAdminAttendanceList'])->name('admin.attendance.list'); //一覧
    /* 
    Route::get('/login', [LoginController::class, 'showLoginForm']);
    Route::get('/attendance/list', [AttendanceController::class, 'list']);
    Route::get('/attendance/{id}', [AttendanceController::class, 'detail']);
    Route::get('/staff/list', [StaffController::class, 'list']);
    Route::get('/attendance/staff/{id}', [AttendanceController::class, 'staffAttendance']);
    Route::get('/request/list', [RequestController::class, 'list']);
    Route::get('/request/approve/{attendance_correct_request}', [RequestController::class, 'approveRequest']);
    */
});
//申請画面
Route::prefix('/request')->group(function () {
    Route::get('/list', [RequestController::class, 'index']); //一覧

});