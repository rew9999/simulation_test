@extends('layouts.app')

@section('title', '取引チャット')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/chat.css') }}">
@endpush

@section('content')
<div class="chat-layout">
    <aside class="chat-sidebar">
        <h3 class="chat-sidebar__title">その他の取引</h3>
        @foreach($otherPurchases as $otherPurchase)
            <a href="{{ route('transaction.show', $otherPurchase->id) }}" class="chat-sidebar__item">
                {{ $otherPurchase->item->name }}
            </a>
        @endforeach
    </aside>

    <div class="chat-main">
        <div class="chat-header">
            <div class="chat-header__user">
                <div class="chat-header__avatar">
                    @if($otherUser->profile_image)
                        <img src="{{ asset('storage/' . $otherUser->profile_image) }}" alt="{{ $otherUser->name }}">
                    @endif
                </div>
                <h2 class="chat-header__title">「{{ $otherUser->name }}」さんとの取引画面</h2>
            </div>
            @if($isBuyer && $purchase->isInTransaction())
                <button type="button" class="btn btn--complete" id="complete-btn">取引を完了する</button>
            @endif
        </div>

        <div class="chat-item-card">
            <div class="chat-item-card__image">
                <img src="{{ asset('storage/' . $purchase->item->image) }}" alt="{{ $purchase->item->name }}">
            </div>
            <div class="chat-item-card__info">
                <div class="chat-item-card__name">{{ $purchase->item->name }}</div>
                <div class="chat-item-card__price">&yen;{{ number_format($purchase->item->price) }}</div>
            </div>
        </div>

        <div class="chat-messages" id="chat-messages">
            @foreach($messages as $message)
                <div class="chat-message {{ $message->user_id === Auth::id() ? 'chat-message--own' : 'chat-message--other' }}">
                    @if($message->user_id !== Auth::id())
                        <div class="chat-message__avatar">
                            @if($message->user->profile_image)
                                <img src="{{ asset('storage/' . $message->user->profile_image) }}" alt="{{ $message->user->name }}">
                            @endif
                        </div>
                    @endif
                    <div class="chat-message__body">
                        <div class="chat-message__user">{{ $message->user->name }}</div>
                        <div class="chat-message__content">
                            {{ $message->content }}
                        </div>
                        @if($message->image)
                            <img src="{{ asset('storage/' . $message->image) }}" alt="" class="chat-message__image">
                        @endif
                        @if($message->user_id === Auth::id())
                            <div class="chat-message__actions">
                                <button type="button" class="chat-message__edit-btn"
                                        data-message-id="{{ $message->id }}"
                                        data-content="{{ $message->content }}">編集</button>
                                <form action="{{ route('transaction.message.delete', $message->id) }}" method="POST" class="chat-message__delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="chat-message__delete-btn">削除</button>
                                </form>
                            </div>
                        @endif
                    </div>
                    @if($message->user_id === Auth::id())
                        <div class="chat-message__avatar">
                            @if(Auth::user()->profile_image)
                                <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" alt="{{ Auth::user()->name }}">
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        @if($errors->any())
            <div class="chat-errors">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div id="edit-form-container" style="display: none;">
            <form action="" method="POST" id="edit-form" class="chat-form" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="chat-form__input-wrapper">
                    <input type="text" name="content" class="chat-form__input" id="edit-content" placeholder="メッセージを編集">
                </div>
                <button type="submit" class="chat-form__send-btn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.01 21L23 12L2.01 3L2 10L17 12L2 14L2.01 21Z" fill="#555"/>
                    </svg>
                </button>
                <button type="button" class="chat-form__cancel-btn" id="edit-cancel">キャンセル</button>
            </form>
        </div>

        @if($purchase->isInTransaction())
            <form action="{{ route('transaction.message.store', $purchase->id) }}" method="POST" enctype="multipart/form-data" class="chat-form" id="chat-form">
                @csrf
                <div class="chat-form__input-wrapper">
                    <input type="text" name="content" class="chat-form__input" id="chat-input"
                           placeholder="取引メッセージを記入してください"
                           value="{{ old('content', $draft) }}">
                </div>
                <label class="chat-form__file-label">
                    画像を追加
                    <input type="file" name="image" accept=".jpeg,.jpg,.png" hidden id="chat-image-input">
                </label>
                <button type="submit" class="chat-form__send-btn">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.01 21L23 12L2.01 3L2 10L17 12L2 14L2.01 21Z" fill="#555"/>
                    </svg>
                </button>
            </form>
        @endif
    </div>
</div>

@if($showRatingModal || ($isBuyer && $purchase->isInTransaction()))
    <div class="modal-overlay" id="rating-modal" style="{{ $showRatingModal ? '' : 'display: none;' }}">
        <div class="modal">
            <h3 class="modal__title">取引が完了しました。</h3>
            <p class="modal__subtitle">今回の取引相手はどうでしたか？</p>
            <form action="{{ route('transaction.complete', $purchase->id) }}" method="POST" id="rating-form">
                @csrf
                <input type="hidden" name="rating" id="rating-input" value="">
                <div class="modal__stars" id="modal-stars">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="modal__star" data-value="{{ $i }}">&#9734;</span>
                    @endfor
                </div>
                <button type="submit" class="btn btn--submit" id="rating-submit">送信する</button>
            </form>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var chatMessages = document.getElementById('chat-messages');
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    var editButtons = document.querySelectorAll('.chat-message__edit-btn');
    var editFormContainer = document.getElementById('edit-form-container');
    var editForm = document.getElementById('edit-form');
    var editContent = document.getElementById('edit-content');
    var editCancel = document.getElementById('edit-cancel');
    var chatForm = document.getElementById('chat-form');

    editButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var messageId = this.getAttribute('data-message-id');
            var content = this.getAttribute('data-content');
            editForm.action = '/transaction/message/' + messageId;
            editContent.value = content;
            editFormContainer.style.display = 'block';
            if (chatForm) chatForm.style.display = 'none';
            editContent.focus();
        });
    });

    if (editCancel) {
        editCancel.addEventListener('click', function() {
            editFormContainer.style.display = 'none';
            if (chatForm) chatForm.style.display = 'flex';
        });
    }

    var stars = document.querySelectorAll('.modal__star');
    var ratingInput = document.getElementById('rating-input');

    stars.forEach(function(star) {
        star.addEventListener('click', function() {
            var value = parseInt(this.getAttribute('data-value'));
            ratingInput.value = value;
            stars.forEach(function(s) {
                var v = parseInt(s.getAttribute('data-value'));
                if (v <= value) {
                    s.innerHTML = '&#9733;';
                    s.classList.add('modal__star--selected');
                } else {
                    s.innerHTML = '&#9734;';
                    s.classList.remove('modal__star--selected');
                }
            });
        });
    });

    var completeBtn = document.getElementById('complete-btn');
    var ratingModal = document.getElementById('rating-modal');

    if (completeBtn && ratingModal) {
        completeBtn.addEventListener('click', function() {
            ratingModal.style.display = 'flex';
        });
    }

    var ratingForm = document.getElementById('rating-form');
    if (ratingForm) {
        ratingForm.addEventListener('submit', function(e) {
            if (!ratingInput.value) {
                e.preventDefault();
                alert('評価を選択してください');
            }
        });
    }

    var chatInput = document.getElementById('chat-input');
    if (chatInput) {
        window.addEventListener('beforeunload', function() {
            var content = chatInput.value;
            if (content) {
                navigator.sendBeacon(
                    '{{ route("transaction.draft", $purchase->id) }}',
                    new URLSearchParams({
                        '_token': '{{ csrf_token() }}',
                        'content': content
                    })
                );
            }
        });
    }
});
</script>
@endpush
