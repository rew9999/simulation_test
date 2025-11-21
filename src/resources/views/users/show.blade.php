@extends('layouts.app')

@section('title', 'マイページ')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/profile.css') }}">
@endpush

@section('content')
    <div class="profile-header">
        <div class="profile-avatar">
            @if($user->profile_image)
                <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}">
            @endif
        </div>
        <div class="profile-info">
            <div class="profile-name">{{ $user->name }}</div>
        </div>
        <a href="{{ route('mypage.edit') }}" class="btn btn--secondary">プロフィールを編集</a>
    </div>

    <div class="tabs">
        <a href="{{ route('mypage', ['tab' => 'sell']) }}"
           class="tab {{ $tab === 'sell' ? 'tab--active' : '' }}">出品した商品</a>
        <a href="{{ route('mypage', ['tab' => 'buy']) }}"
           class="tab {{ $tab === 'buy' ? 'tab--active' : '' }}">購入した商品</a>
    </div>

    <div class="items-grid">
        @foreach($items as $item)
            <div class="item-card">
                <a href="{{ route('items.show', $item->id) }}" class="item-card__link">
                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="item-card__image">
                    <div class="item-card__info">
                        <div class="item-card__name">{{ $item->name }}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endsection
