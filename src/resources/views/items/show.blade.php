@extends('layouts.app')

@section('title', $item->name)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/items-show.css') }}">
@endpush

@section('content')
    <div class="item-detail">
        <div>
            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="item-detail__image">
        </div>
        <div>
            <h1 class="item-detail__name">{{ $item->name }}</h1>
            @if($item->brand)
                <div class="item-detail__brand">{{ $item->brand }}</div>
            @endif
            <div class="item-detail__price">¥{{ number_format($item->price) }}(税込)</div>

            <div class="item-detail__actions">
                @auth
                    <form action="{{ route('items.like', $item->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="action-btn {{ $isLiked ? 'action-btn--liked' : '' }}">
                            <span style="font-size: 24px;">{{ $isLiked ? '★' : '☆' }}</span>
                            <span>{{ $likesCount }}</span>
                        </button>
                    </form>
                @else
                    <div class="action-btn">
                        <span style="font-size: 24px;">☆</span>
                        <span>{{ $likesCount }}</span>
                    </div>
                @endauth
                <div class="action-btn">
                    <span style="font-size: 24px;">💬</span>
                    <span>{{ $commentsCount }}</span>
                </div>
            </div>

            @auth
                @if(!$item->purchase)
                    <a href="{{ route('purchase.show', $item->id) }}" class="btn btn--primary item-detail__purchase">購入手続きへ</a>
                @else
                    <button class="btn btn--secondary item-detail__purchase" disabled>売り切れ</button>
                @endif
            @endauth

            <div class="item-detail__section">
                <h3>商品説明</h3>
                <p>{{ $item->description }}</p>
            </div>

            <div class="item-detail__section">
                <h3>商品の情報</h3>
                <div class="item-detail__categories">
                    <strong>カテゴリー：</strong>
                    @foreach($item->categories as $category)
                        <span class="category-tag">{{ $category->name }}</span>
                    @endforeach
                </div>
                <div style="margin-top: 10px;">
                    <strong>商品の状態：</strong>{{ $item->condition }}
                </div>
            </div>

            <div class="comments-section">
                <h3>コメント ({{ $commentsCount }})</h3>
                @foreach($item->comments as $comment)
                    <div class="comment">
                        <div class="comment__avatar">
                            @if($comment->user->profile_image)
                                <img src="{{ asset('storage/' . $comment->user->profile_image) }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                            @endif
                        </div>
                        <div class="comment__content">
                            <div class="comment__user">{{ $comment->user->name }}</div>
                            <div>{{ $comment->content }}</div>
                        </div>
                    </div>
                @endforeach

                @auth
                    @if ($errors->any())
                        <div class="error-messages">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('items.comment', $item->id) }}" method="POST" class="comment-form">
                        @csrf
                        <h4>商品へのコメント</h4>
                        <textarea name="content" placeholder="コメントを入力"></textarea>
                        <button type="submit" class="btn btn--primary">コメントを送信する</button>
                    </form>
                @endauth
            </div>
        </div>
    </div>
@endsection
