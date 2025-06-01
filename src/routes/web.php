<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ExportController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;



/******* 認証関連 *******/
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/attendance'); 
})->middleware(['auth', 'signed'])->name('verification.verify');

// 認証メールの再送信
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送信しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');



/******* 一般ユーザー用 *******/
Route::post('login', [LoginController::class, 'login']); //ログイン
Route::post('logout', [LoginController::class, 'logout'])->name('logout'); //ログアウト
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'create']); //出勤登録ページ
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.action'); //出勤登録処理
    Route::get('/attendance/list', [AttendanceController::class, 'showAttendanceList'])->name('attendance.list'); //勤怠一覧ページ
    Route::get('/attendance/{id}', [AttendanceController::class, 'AttendanceDetail'])->name('attendance.detail');//勤怠詳細ページ
    Route::post('/attendance/{id}', [AttendanceController::class, 'editAttendanceDetail'])->name('detail.request');//修正申請
});


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
//CSV出力
Route::get('/export-attendance', [ExportController::class, 'export'])->name('export.attendances');



/****** ミドルウェアでユーザーか管理者か区別 ******/
//申請一覧ページ
Route::middleware(['auth'])->get('/stamp_correction_request/list', [RequestController::class, 'showRequestList'])
    ->name('request.list'); 
