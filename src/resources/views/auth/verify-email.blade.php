@extends('layouts.app')

@section('title', 'メール認証')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/verify-email.css') }}">
@endpush

@section('content')
    <div class="auth-container">
        <h2>メール認証</h2>

        <div class="verification-notice">
            <p>ご登録ありがとうございます。</p>
            <p>メールアドレスの確認が必要です。</p>
            <p>下記ボタンを押して、送信されたメール内のリンクをクリックしてください。</p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert--success">
                認証メールを送信しました。メールをご確認ください。
            </div>
        @endif

        <div class="verification-actions">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn--primary">
                    {{ session('status') == 'verification-link-sent' ? '認証メールを再送する' : '認証はこちらから' }}
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="btn btn--secondary">ログアウト</button>
            </form>
        </div>
    </div>
@endsection
