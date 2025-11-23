@extends('layouts.app')

@section('title', 'メール認証')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/verify-email.css') }}">
@endpush

@section('content')
    <div class="verify-email-container">
        <div class="verify-email-content">
            <p class="verify-email-message">
                登録していただいたメールアドレスに認証メールを送付しました。<br>
                メール認証を完了してください。
            </p>

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="verify-email-button">
                    認証はこちらから
                </button>
            </form>

            <form method="POST" action="{{ route('verification.send') }}" class="resend-form">
                @csrf
                <button type="submit" class="resend-link">
                    認証メールを再送する
                </button>
            </form>
        </div>
    </div>
@endsection
