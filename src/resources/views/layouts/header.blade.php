<!--その他画面用ヘッダー-->
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>COACHTECH</title>
        <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
        <link rel="stylesheet" href="{{ asset('css/common.css') }}" />
        @yield('css')
    </head>
    <body>
        <header class="header">
            <div class="header__logo">
                <a href="">
                    <img src="{{ asset('storage/logo.svg') }}" alt="logo">
                </a>
            </div>

            <div class="header__inner">
                @if(Auth::check())
                    <nav class="header__nav">
                        <a class="header__link" href="">勤怠</a>
                        <a class="header__link" href="">勤怠一覧</a>
                        <a class="header__link" href="">申請</a>

                        <form class="header__logout-form" action="" method="POST">
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