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
            @if($user->average_rating)
                <div class="profile-rating">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="star {{ $i <= $user->average_rating ? 'star--filled' : '' }}">&#9733;</span>
                    @endfor
                </div>
            @endif
        </div>
        <a href="{{ route('mypage.edit') }}" class="btn btn--secondary">プロフィールを編集</a>
    </div>

    <div class="tabs">
        <a href="{{ route('mypage', ['tab' => 'sell']) }}"
           class="tab {{ $tab === 'sell' ? 'tab--active' : '' }}">出品した商品</a>
        <a href="{{ route('mypage', ['tab' => 'buy']) }}"
           class="tab {{ $tab === 'buy' ? 'tab--active' : '' }}">購入した商品</a>
        <a href="{{ route('mypage', ['tab' => 'transaction']) }}"
           class="tab {{ $tab === 'transaction' ? 'tab--active' : '' }}">
            取引中の商品
            @if(isset($totalUnread) && $totalUnread > 0)
                <span class="tab__badge">{{ $totalUnread }}</span>
            @endif
        </a>
    </div>

    <div class="items-grid">
        @if($tab === 'transaction')
            @foreach($purchases as $purchase)
                <div class="item-card">
                    <a href="{{ route('transaction.show', $purchase->id) }}" class="item-card__link">
                        <div class="item-card__image-wrapper">
                            <img src="{{ asset('storage/' . $purchase->item->image) }}" alt="{{ $purchase->item->name }}" class="item-card__image">
                            @if(isset($unreadCounts[$purchase->id]) && $unreadCounts[$purchase->id] > 0)
                                <span class="item-card__badge">{{ $unreadCounts[$purchase->id] }}</span>
                            @endif
                        </div>
                        <div class="item-card__info">
                            <div class="item-card__name">{{ $purchase->item->name }}</div>
                        </div>
                    </a>
                </div>
            @endforeach
        @else
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
        @endif
    </div>
@endsection
