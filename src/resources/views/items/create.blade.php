@extends('layouts.app')

@section('title', '商品の出品')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/items-create.css') }}">
@endpush

@section('content')
    <h2>商品の出品</h2>

    @if ($errors->any())
        <div class="error-messages">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data" class="sell-form">
        @csrf

        <div class="form-group">
            <label>商品画像</label>
            <div class="image-upload" id="image-upload">
                <p>クリックして画像を選択<br>またはドラッグ＆ドロップ</p>
                <input type="file" name="image" accept="image/*" required>
            </div>
            <img id="image-preview" class="image-preview" src="" alt="プレビュー">
        </div>

        <h3 class="section-title">商品の詳細</h3>

        <div class="form-group">
            <label>カテゴリー</label>
            <div class="categories-grid">
                @foreach($categories as $category)
                    <label class="category-checkbox">
                        <input type="checkbox" name="categories[]" value="{{ $category->id }}">
                        {{ $category->name }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="form-group">
            <label>商品の状態</label>
            <div class="conditions-list">
                @foreach($conditions as $condition)
                    <label class="condition-radio">
                        <input type="radio" name="condition" value="{{ $condition }}" required>
                        {{ $condition }}
                    </label>
                @endforeach
            </div>
        </div>

        <h3 class="section-title">商品名と説明</h3>

        <div class="form-group">
            <label for="name">商品名</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
            <label for="brand">ブランド名</label>
            <input type="text" id="brand" name="brand" value="{{ old('brand') }}">
        </div>

        <div class="form-group">
            <label for="description">商品の説明</label>
            <textarea id="description" name="description" required>{{ old('description') }}</textarea>
        </div>

        <div class="form-group">
            <label for="price">販売価格</label>
            <div class="price-input">
                <input type="number" id="price" name="price" value="{{ old('price') }}" min="0" required>
            </div>
        </div>

        <button type="submit" class="btn btn--primary submit-btn">出品する</button>
    </form>
@endsection

@push('scripts')
<script>
    const imageInput = document.querySelector('input[name="image"]');
    const imagePreview = document.getElementById('image-preview');

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush
