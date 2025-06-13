<!--ユーザー用共通ヘッダー-->
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>COACHTECH</title>
        <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
        <link rel="stylesheet" href="{{ asset('css/user_header.css') }}" />
        @yield('css')
    </head>
    <body>
        <header class="header">
            <div class="header__logo">
                <img src="{{ asset('storage/logo.svg') }}" alt="logo">
            </div>
            <div class="header__inner">
                @if(Auth::check())
                    @php
                        $attendance = Auth::user()->todayAttendance;
                        $isClockedOut = $attendance && $attendance->end_time;
                    @endphp
                    <nav class="header__nav">
                        @if($isClockedOut)
                            <a class="header__link" href="/attendance/list">今月の出勤一覧</a>
                            <a class="header__link" href="/stamp_correction_request/list">申請一覧</a>
                        @else
                            <a class="header__link" href="/attendance">勤怠</a>
                            <a class="header__link" href="/attendance/list">勤怠一覧</a>
                            <a class="header__link" href="/stamp_correction_request/list">申請</a>
                        @endif
                        <form class="header__logout-form" action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="header__logout-button" type="submit">ログアウト</button>
                        </form>
                    </nav>
                @endif
            </div>
        </header>
        <main>
            @yield('content')
            @yield('js')
        </main>
    </body>
</html>