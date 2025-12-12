<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'COACHTECH フリマ')</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @stack('styles')
</head>
<body>
    <header class="header">
        <a href="{{ route('items.index') }}" class="header__logo">COACHTECH</a>

        <form class="header__search" action="{{ route('items.index') }}" method="GET">
            <input type="text" name="keyword" placeholder="なにをお探しですか？" value="{{ request('keyword') }}">
        </form>

        <nav class="header__nav">
            @auth
                <form action="{{ route('logout') }}" method="POST" class="inline-form">
                    @csrf
                    <button type="submit">ログアウト</button>
                </form>
                <a href="{{ route('mypage') }}">マイページ</a>
                <a href="{{ route('items.create') }}" class="btn btn--primary">出品</a>
            @else
                <a href="{{ route('login') }}">ログイン</a>
                <a href="{{ route('register') }}">会員登録</a>
            @endauth
        </nav>
    </header>

    <main class="container">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
