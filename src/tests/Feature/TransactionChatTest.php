<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Message;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TransactionChatTest extends TestCase
{
    use RefreshDatabase;

    private function createTransaction(): array
    {
        $seller = User::factory()->create(['email_verified_at' => now()]);
        $buyer = User::factory()->create(['email_verified_at' => now()]);
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

    public function test_buyer_can_view_chat_page(): void
    {
        ['buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $response = $this->actingAs($buyer)->get('/transaction/' . $purchase->id);

        $response->assertStatus(200);
        $response->assertViewIs('transactions.chat');
    }

    public function test_seller_can_view_chat_page(): void
    {
        ['seller' => $seller, 'purchase' => $purchase] = $this->createTransaction();

        $response = $this->actingAs($seller)->get('/transaction/' . $purchase->id);

        $response->assertStatus(200);
        $response->assertViewIs('transactions.chat');
    }

    public function test_guest_cannot_view_chat_page(): void
    {
        ['purchase' => $purchase] = $this->createTransaction();

        $response = $this->get('/transaction/' . $purchase->id);

        $response->assertRedirect('/login');
    }

    public function test_unauthorized_user_cannot_view_chat_page(): void
    {
        ['purchase' => $purchase] = $this->createTransaction();
        $otherUser = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($otherUser)->get('/transaction/' . $purchase->id);

        $response->assertStatus(403);
    }

    public function test_buyer_can_send_message(): void
    {
        ['buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $response = $this->actingAs($buyer)->post('/transaction/' . $purchase->id . '/message', [
            'content' => 'テストメッセージ',
        ]);

        $response->assertRedirect('/transaction/' . $purchase->id);
        $this->assertDatabaseHas('messages', [
            'user_id' => $buyer->id,
            'purchase_id' => $purchase->id,
            'content' => 'テストメッセージ',
        ]);
    }

    public function test_seller_can_send_message(): void
    {
        ['seller' => $seller, 'purchase' => $purchase] = $this->createTransaction();

        $response = $this->actingAs($seller)->post('/transaction/' . $purchase->id . '/message', [
            'content' => '出品者からのメッセージ',
        ]);

        $response->assertRedirect('/transaction/' . $purchase->id);
        $this->assertDatabaseHas('messages', [
            'user_id' => $seller->id,
            'purchase_id' => $purchase->id,
            'content' => '出品者からのメッセージ',
        ]);
    }

    public function test_message_content_is_required(): void
    {
        ['buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $response = $this->actingAs($buyer)->post('/transaction/' . $purchase->id . '/message', [
            'content' => '',
        ]);

        $response->assertSessionHasErrors(['content']);
    }

    public function test_message_content_max_400_characters(): void
    {
        ['buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $response = $this->actingAs($buyer)->post('/transaction/' . $purchase->id . '/message', [
            'content' => str_repeat('あ', 401),
        ]);

        $response->assertSessionHasErrors(['content']);
    }

    public function test_message_with_400_characters_is_accepted(): void
    {
        ['buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $response = $this->actingAs($buyer)->post('/transaction/' . $purchase->id . '/message', [
            'content' => str_repeat('あ', 400),
        ]);

        $response->assertSessionMissing('errors');
        $this->assertDatabaseHas('messages', [
            'user_id' => $buyer->id,
            'purchase_id' => $purchase->id,
        ]);
    }

    public function test_can_send_message_with_image(): void
    {
        Storage::fake('public');
        ['buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $response = $this->actingAs($buyer)->post('/transaction/' . $purchase->id . '/message', [
            'content' => '画像付きメッセージ',
            'image' => UploadedFile::fake()->create('test.png', 100, 'image/png'),
        ]);

        $response->assertRedirect('/transaction/' . $purchase->id);
        $message = Message::where('purchase_id', $purchase->id)->first();
        $this->assertNotNull($message->image);
    }

    public function test_image_must_be_jpeg_or_png(): void
    {
        Storage::fake('public');
        ['buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $response = $this->actingAs($buyer)->post('/transaction/' . $purchase->id . '/message', [
            'content' => 'テスト',
            'image' => UploadedFile::fake()->create('test.gif', 100, 'image/gif'),
        ]);

        $response->assertSessionHasErrors(['image']);
    }

    public function test_user_can_edit_own_message(): void
    {
        ['buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();
        $message = Message::create([
            'user_id' => $buyer->id,
            'purchase_id' => $purchase->id,
            'content' => '元のメッセージ',
        ]);

        $response = $this->actingAs($buyer)->put('/transaction/message/' . $message->id, [
            'content' => '編集後のメッセージ',
        ]);

        $response->assertRedirect('/transaction/' . $purchase->id);
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'content' => '編集後のメッセージ',
        ]);
    }

    public function test_user_cannot_edit_other_users_message(): void
    {
        ['seller' => $seller, 'buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();
        $message = Message::create([
            'user_id' => $buyer->id,
            'purchase_id' => $purchase->id,
            'content' => '購入者のメッセージ',
        ]);

        $response = $this->actingAs($seller)->put('/transaction/message/' . $message->id, [
            'content' => '編集を試みる',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'content' => '購入者のメッセージ',
        ]);
    }

    public function test_user_can_delete_own_message(): void
    {
        ['buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();
        $message = Message::create([
            'user_id' => $buyer->id,
            'purchase_id' => $purchase->id,
            'content' => '削除するメッセージ',
        ]);

        $response = $this->actingAs($buyer)->delete('/transaction/message/' . $message->id);

        $response->assertRedirect('/transaction/' . $purchase->id);
        $this->assertDatabaseMissing('messages', ['id' => $message->id]);
    }

    public function test_user_cannot_delete_other_users_message(): void
    {
        ['seller' => $seller, 'buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();
        $message = Message::create([
            'user_id' => $buyer->id,
            'purchase_id' => $purchase->id,
            'content' => '購入者のメッセージ',
        ]);

        $response = $this->actingAs($seller)->delete('/transaction/message/' . $message->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('messages', ['id' => $message->id]);
    }

    public function test_unread_messages_are_marked_as_read_on_page_view(): void
    {
        ['seller' => $seller, 'buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();
        Message::create([
            'user_id' => $seller->id,
            'purchase_id' => $purchase->id,
            'content' => '出品者のメッセージ',
            'is_read' => false,
        ]);

        $this->actingAs($buyer)->get('/transaction/' . $purchase->id);

        $this->assertDatabaseHas('messages', [
            'purchase_id' => $purchase->id,
            'user_id' => $seller->id,
            'is_read' => true,
        ]);
    }

    public function test_sidebar_shows_other_transactions(): void
    {
        ['seller' => $seller, 'buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();
        $otherItem = Item::factory()->create(['user_id' => $seller->id]);
        $otherPurchase = Purchase::create([
            'user_id' => $buyer->id,
            'item_id' => $otherItem->id,
            'payment_method' => 'コンビニ払い',
            'postal_code' => '123-4567',
            'address' => '東京都',
            'building' => null,
            'status' => '取引中',
        ]);

        $response = $this->actingAs($buyer)->get('/transaction/' . $purchase->id);

        $response->assertSee($otherItem->name);
    }

    public function test_draft_is_saved(): void
    {
        ['buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $response = $this->actingAs($buyer)->post('/transaction/' . $purchase->id . '/draft', [
            'content' => '下書きテスト',
        ]);

        $response->assertJson(['status' => 'ok']);
    }

    public function test_draft_is_restored_on_page_load(): void
    {
        ['buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $this->actingAs($buyer)->post('/transaction/' . $purchase->id . '/draft', [
            'content' => '下書き復元テスト',
        ]);

        $response = $this->actingAs($buyer)->get('/transaction/' . $purchase->id);

        $response->assertSee('下書き復元テスト');
    }

    public function test_draft_is_cleared_after_sending_message(): void
    {
        ['buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $this->actingAs($buyer)->post('/transaction/' . $purchase->id . '/draft', [
            'content' => '下書き',
        ]);

        $this->actingAs($buyer)->post('/transaction/' . $purchase->id . '/message', [
            'content' => '送信メッセージ',
        ]);

        $response = $this->actingAs($buyer)->get('/transaction/' . $purchase->id);

        $response->assertDontSee('value="下書き"', false);
    }
}
