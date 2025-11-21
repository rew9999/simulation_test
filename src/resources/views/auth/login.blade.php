@extends('layouts.app')

@section('title', 'ログイン')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/auth.css') }}">
@endpush

@section('content')
    <div class="auth-form">
        <h1>ログイン</h1>

        @if ($errors->any())
            <div class="error-messages">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn--primary submit-btn">ログインする</button>
        </form>

        <div class="auth-link">
            <a href="{{ route('register') }}">会員登録はこちら</a>
        </div>
    </div>
@endsection
