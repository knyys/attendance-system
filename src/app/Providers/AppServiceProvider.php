<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\Attendance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // ログインしている場合、共通のビューに本日分の勤怠情報を渡す
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $today = Carbon::today();
                $attendance = Attendance::where('user_id', Auth::id())
                    ->whereDate('date', $today)
                    ->first();
                $view->with('attendance', $attendance);
            }
        });
    }
    

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
