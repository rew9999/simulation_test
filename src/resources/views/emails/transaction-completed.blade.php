<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <h1>取引完了のお知らせ</h1>
    <p>{{ $purchase->item->user->name }}様</p>
    <p>「{{ $purchase->item->name }}」の取引が完了しました。</p>
    <p>購入者: {{ $buyer->name }}様</p>
    <p>取引チャットにて評価をお願いいたします。</p>
    <p>COACHTECH フリマ</p>
</body>
</html>
