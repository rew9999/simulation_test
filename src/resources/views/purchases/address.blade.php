@extends('layouts.app')

@section('title', '住所の変更')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/address.css') }}">
@endpush

@section('content')
    <h2>住所の変更</h2>

    @if ($errors->any())
        <div class="error-messages">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('purchase.address.update', $item->id) }}" method="POST" class="address-form">
        @csrf
        <div class="form-group">
            <label for="postal_code">郵便番号</label>
            <input type="text" id="postal_code" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}" required>
        </div>

        <div class="form-group">
            <label for="address">住所</label>
            <input type="text" id="address" name="address" value="{{ old('address', $user->address) }}" required>
        </div>

        <div class="form-group">
            <label for="building">建物名</label>
            <input type="text" id="building" name="building" value="{{ old('building', $user->building) }}">
        </div>

        <button type="submit" class="btn btn--primary submit-btn">更新する</button>
    </form>
@endsection
