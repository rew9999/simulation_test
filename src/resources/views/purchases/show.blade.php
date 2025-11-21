@extends('layouts.app')

@section('title', '購入手続き')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/purchase.css') }}">
@endpush

@section('content')
    <h2>購入手続き</h2>

    @if ($errors->any())
        <div class="error-messages">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('purchase.store', $item->id) }}" method="POST">
        @csrf
        <div class="purchase-page">
            <div>
                <div class="purchase-section">
                    <div class="item-summary">
                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="item-summary__image">
                        <div class="item-summary__info">
                            <div class="item-summary__name">{{ $item->name }}</div>
                            <div class="item-summary__price">¥{{ number_format($item->price) }}</div>
                        </div>
                    </div>
                </div>

                <div class="purchase-section">
                    <h3>支払い方法</h3>
                    <div class="payment-methods">
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="コンビニ払い" required>
                            コンビニ払い
                        </label>
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="カード支払い">
                            カード支払い
                        </label>
                    </div>
                </div>

                <div class="purchase-section">
                    <div class="address-section">
                        <div class="address-info">
                            <h3>配送先</h3>
                            <p>〒{{ session('postal_code', $user->postal_code ?? '') }}</p>
                            <p>{{ session('address', $user->address ?? '') }}</p>
                            <p>{{ session('building', $user->building ?? '') }}</p>
                            <input type="hidden" name="postal_code" value="{{ session('postal_code', $user->postal_code ?? '') }}">
                            <input type="hidden" name="address" value="{{ session('address', $user->address ?? '') }}">
                            <input type="hidden" name="building" value="{{ session('building', $user->building ?? '') }}">
                        </div>
                        <a href="{{ route('purchase.address', $item->id) }}" class="btn btn--secondary">変更する</a>
                    </div>
                </div>
            </div>

            <div>
                <div class="order-summary">
                    <div class="order-summary__row">
                        <span>商品代金</span>
                        <span>¥{{ number_format($item->price) }}</span>
                    </div>
                    <div class="order-summary__row">
                        <span>支払い方法</span>
                        <span id="selected-payment">選択してください</span>
                    </div>
                    <div class="order-summary__row">
                        <span>合計</span>
                        <span class="order-summary__total">¥{{ number_format($item->price) }}</span>
                    </div>
                    <button type="submit" class="btn btn--primary purchase-btn">購入する</button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('selected-payment').textContent = this.value;
        });
    });
</script>
@endpush
