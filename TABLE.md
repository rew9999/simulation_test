# テーブル設計書

## 1. users テーブル
ユーザー情報（プロフィール含む）

- id (unsigned bigint, PRIMARY KEY, NOT NULL)
- name (string, NOT NULL) - ユーザー名
- email (string, UNIQUE, NOT NULL) - メールアドレス
- email_verified_at (timestamp, nullable) - メール認証日時
- password (string, NOT NULL) - パスワード
- profile_image (string, nullable) - プロフィール画像パス
- postal_code (string, nullable) - 郵便番号
- address (string, nullable) - 住所
- building (string, nullable) - 建物名
- created_at (timestamp)
- updated_at (timestamp)


## 2. categories テーブル
カテゴリマスタ

- id (unsigned bigint, PRIMARY KEY, NOT NULL)
- name (string, NOT NULL) - カテゴリ名
- created_at (timestamp)
- updated_at (timestamp)


## 3. items テーブル
商品情報

- id (unsigned bigint, PRIMARY KEY, NOT NULL)
- user_id (unsigned bigint, NOT NULL, FOREIGN KEY -> users(id)) - 出品者
- name (string, NOT NULL) - 商品名
- brand (string, nullable) - ブランド名
- description (text, NOT NULL) - 商品説明
- price (integer, NOT NULL) - 価格
- condition (string, NOT NULL) - 商品の状態
- image (string, NOT NULL) - 商品画像パス
- created_at (timestamp)
- updated_at (timestamp)


## 4. category_item テーブル
商品・カテゴリ中間テーブル（多対多）

- id (unsigned bigint, PRIMARY KEY, NOT NULL)
- category_id (unsigned bigint, NOT NULL, FOREIGN KEY -> categories(id))
- item_id (unsigned bigint, NOT NULL, FOREIGN KEY -> items(id))


## 5. purchases テーブル
購入情報

- id (unsigned bigint, PRIMARY KEY, NOT NULL)
- user_id (unsigned bigint, NOT NULL, FOREIGN KEY -> users(id)) - 購入者
- item_id (unsigned bigint, UNIQUE, NOT NULL, FOREIGN KEY -> items(id)) - 商品
- payment_method (string, NOT NULL) - 支払い方法
- postal_code (string, NOT NULL) - 配送先郵便番号
- address (string, NOT NULL) - 配送先住所
- building (string, nullable) - 配送先建物名
- created_at (timestamp)
- updated_at (timestamp)


## 6. likes テーブル
いいね情報

- id (unsigned bigint, PRIMARY KEY, NOT NULL)
- user_id (unsigned bigint, NOT NULL, FOREIGN KEY -> users(id))
- item_id (unsigned bigint, NOT NULL, FOREIGN KEY -> items(id))
- created_at (timestamp)
- updated_at (timestamp)

※ user_id + item_id の組み合わせでユニーク制約推奨


## 7. comments テーブル
コメント情報

- id (unsigned bigint, PRIMARY KEY, NOT NULL)
- user_id (unsigned bigint, NOT NULL, FOREIGN KEY -> users(id))
- item_id (unsigned bigint, NOT NULL, FOREIGN KEY -> items(id))
- content (string(255), NOT NULL) - コメント内容
- created_at (timestamp)
- updated_at (timestamp)
