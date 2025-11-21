@extends('layouts.app')

@section('title', '商品一覧')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/items-index.css') }}">
@endpush

@section('content')
    <div class="tabs">
        <a href="{{ route('items.index', ['tab' => 'recommend', 'keyword' => $keyword]) }}"
           class="tab {{ $tab === 'recommend' ? 'tab--active' : '' }}">おすすめ</a>
        @auth
            <a href="{{ route('items.index', ['tab' => 'mylist', 'keyword' => $keyword]) }}"
               class="tab {{ $tab === 'mylist' ? 'tab--active' : '' }}">マイリスト</a>
        @endauth
    </div>

    <div class="items-grid">
        @foreach($items as $item)
            <div class="item-card">
                <a href="{{ route('items.show', $item->id) }}" class="item-card__link">
                    <div style="position: relative;">
                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="item-card__image">
                        @if($item->purchase)
                            <div class="item-card__sold">Sold</div>
                        @endif
                    </div>
                    <div class="item-card__info">
                        <div class="item-card__name">{{ $item->name }}</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endsection
