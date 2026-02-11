<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Message;
use App\Models\Purchase;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TransactionMypageTest extends TestCase
{
    use RefreshDatabase;

    private function createTransaction(User $seller = null, User $buyer = null): array
    {
        $seller = $seller ?? User::factory()->create(['email_verified_at' => now()]);
        $buyer = $buyer ?? User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create(['user_id' => $seller->id]);
        $purchase = Purchase::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'payment_method' => 'コンビニ払い',
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区道玄坂1-2-3',
            'building' => 'テストビル101',
            'status' => '取引中',
        ]);

        return compact('seller', 'buyer', 'item', 'purchase');
    }

    public function test_transaction_tab_is_visible_on_mypage(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->get('/mypage?tab=transaction');

        $response->assertStatus(200);
        $response->assertSee('取引中の商品');
    }

    public function test_transaction_tab_shows_purchases_for_buyer(): void
    {
        ['buyer' => $buyer, 'item' => $item] = $this->createTransaction();

        $response = $this->actingAs($buyer)->get('/mypage?tab=transaction');

        $response->assertStatus(200);
        $response->assertSee($item->name);
    }

    public function test_transaction_tab_shows_purchases_for_seller(): void
    {
        ['seller' => $seller, 'item' => $item] = $this->createTransaction();

        $response = $this->actingAs($seller)->get('/mypage?tab=transaction');

        $response->assertStatus(200);
        $response->assertSee($item->name);
    }

    public function test_completed_transactions_not_shown_in_transaction_tab(): void
    {
        $seller = User::factory()->create(['email_verified_at' => now()]);
        $buyer = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create(['user_id' => $seller->id]);
        Purchase::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'payment_method' => 'コンビニ払い',
            'postal_code' => '123-4567',
            'address' => '東京都',
            'building' => null,
            'status' => '完了',
        ]);

        $response = $this->actingAs($buyer)->get('/mypage?tab=transaction');

        $response->assertDontSee($item->name);
    }

    public function test_unread_badge_shows_on_transaction_tab(): void
    {
        ['seller' => $seller, 'buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        Message::create([
            'user_id' => $seller->id,
            'purchase_id' => $purchase->id,
            'content' => '未読メッセージ',
            'is_read' => false,
        ]);

        $response = $this->actingAs($buyer)->get('/mypage?tab=transaction');

        $response->assertSee('tab__badge');
    }

    public function test_no_unread_badge_when_all_messages_read(): void
    {
        ['seller' => $seller, 'buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        Message::create([
            'user_id' => $seller->id,
            'purchase_id' => $purchase->id,
            'content' => '既読メッセージ',
            'is_read' => true,
        ]);

        $response = $this->actingAs($buyer)->get('/mypage?tab=transaction');

        $response->assertDontSee('tab__badge');
    }

    public function test_unread_badge_shows_on_item_card(): void
    {
        ['seller' => $seller, 'buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        Message::create([
            'user_id' => $seller->id,
            'purchase_id' => $purchase->id,
            'content' => '未読メッセージ',
            'is_read' => false,
        ]);

        $response = $this->actingAs($buyer)->get('/mypage?tab=transaction');

        $response->assertSee('item-card__badge');
    }

    public function test_transactions_sorted_by_latest_message(): void
    {
        $seller = User::factory()->create(['email_verified_at' => now()]);
        $buyer = User::factory()->create(['email_verified_at' => now()]);

        $item1 = Item::factory()->create(['user_id' => $seller->id, 'name' => '古い取引の商品']);
        $purchase1 = Purchase::create([
            'user_id' => $buyer->id,
            'item_id' => $item1->id,
            'payment_method' => 'コンビニ払い',
            'postal_code' => '123-4567',
            'address' => '東京都',
            'building' => null,
            'status' => '取引中',
        ]);

        $item2 = Item::factory()->create(['user_id' => $seller->id, 'name' => '新しい取引の商品']);
        $purchase2 = Purchase::create([
            'user_id' => $buyer->id,
            'item_id' => $item2->id,
            'payment_method' => 'コンビニ払い',
            'postal_code' => '123-4567',
            'address' => '東京都',
            'building' => null,
            'status' => '取引中',
        ]);

        Carbon::setTestNow(now()->subHour());
        Message::create([
            'user_id' => $seller->id,
            'purchase_id' => $purchase1->id,
            'content' => '古いメッセージ',
        ]);

        Carbon::setTestNow(now()->addHour());
        Message::create([
            'user_id' => $seller->id,
            'purchase_id' => $purchase2->id,
            'content' => '新しいメッセージ',
        ]);
        Carbon::setTestNow();

        $response = $this->actingAs($buyer)->get('/mypage?tab=transaction');

        $purchases = $response->viewData('purchases');
        $purchaseIds = $purchases->pluck('id')->values()->toArray();
        $this->assertEquals([$purchase2->id, $purchase1->id], $purchaseIds);
    }

    public function test_star_rating_displayed_on_mypage(): void
    {
        $seller = User::factory()->create(['email_verified_at' => now()]);
        $buyer = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create(['user_id' => $seller->id]);
        $purchase = Purchase::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'payment_method' => 'コンビニ払い',
            'postal_code' => '123-4567',
            'address' => '東京都',
            'building' => null,
            'status' => '完了',
        ]);

        Rating::create([
            'purchase_id' => $purchase->id,
            'rater_user_id' => $buyer->id,
            'rated_user_id' => $seller->id,
            'rating' => 4,
        ]);

        $response = $this->actingAs($seller)->get('/mypage');

        $response->assertSee('star--filled');
    }

    public function test_no_star_rating_when_unrated(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->get('/mypage');

        $response->assertDontSee('profile-rating');
    }

    public function test_transaction_tab_links_to_chat_page(): void
    {
        ['buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $response = $this->actingAs($buyer)->get('/mypage?tab=transaction');

        $response->assertSee('/transaction/' . $purchase->id);
    }
}
