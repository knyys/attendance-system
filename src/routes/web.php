<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\User\AttendanceController;
use App\Http\Controllers\User\RequestController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/* 一般ユーザー用 */
//会員登録
//Route::get('register', [RegisterController::class, 'create']);

//ログイン
Route::post('login', [LoginController::class, 'login']);

//勤怠画面
Route::prefix('/attendance')->group(function () {
    Route::get('', [AttendanceController::class, 'create']); //登録
    Route::get('/detail',[AttendanceController::class, 'detail']); //詳細
    Route::get('/list', [AttendanceController::class, 'index']); //一覧
});






/* 管理者用 */
Route::prefix('admin')->group(function () {

    Route::get('/login', function () {
        return view('auth.login'); 
    });
    Route::post('login', [LoginController::class, 'adminLogin']);


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