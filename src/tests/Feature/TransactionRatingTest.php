<?php

namespace Tests\Feature;

use App\Mail\TransactionCompletedMail;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TransactionRatingTest extends TestCase
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

    public function test_buyer_can_submit_rating(): void
    {
        Mail::fake();
        ['buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $response = $this->actingAs($buyer)->post('/transaction/' . $purchase->id . '/complete', [
            'rating' => 5,
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('ratings', [
            'purchase_id' => $purchase->id,
            'rater_user_id' => $buyer->id,
            'rating' => 5,
        ]);
    }

    public function test_seller_can_submit_rating(): void
    {
        ['seller' => $seller, 'buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $purchase->update(['status' => '完了']);

        Rating::create([
            'purchase_id' => $purchase->id,
            'rater_user_id' => $buyer->id,
            'rated_user_id' => $seller->id,
            'rating' => 4,
        ]);

        $response = $this->actingAs($seller)->post('/transaction/' . $purchase->id . '/complete', [
            'rating' => 3,
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('ratings', [
            'purchase_id' => $purchase->id,
            'rater_user_id' => $seller->id,
            'rated_user_id' => $buyer->id,
            'rating' => 3,
        ]);
    }

    public function test_rating_is_required(): void
    {
        ['buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $response = $this->actingAs($buyer)->post('/transaction/' . $purchase->id . '/complete', [
            'rating' => '',
        ]);

        $response->assertSessionHasErrors(['rating']);
    }

    public function test_rating_must_be_at_least_1(): void
    {
        ['buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $response = $this->actingAs($buyer)->post('/transaction/' . $purchase->id . '/complete', [
            'rating' => 0,
        ]);

        $response->assertSessionHasErrors(['rating']);
    }

    public function test_rating_must_be_at_most_5(): void
    {
        ['buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $response = $this->actingAs($buyer)->post('/transaction/' . $purchase->id . '/complete', [
            'rating' => 6,
        ]);

        $response->assertSessionHasErrors(['rating']);
    }

    public function test_buyer_rating_sets_status_to_completed(): void
    {
        Mail::fake();
        ['buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $this->actingAs($buyer)->post('/transaction/' . $purchase->id . '/complete', [
            'rating' => 4,
        ]);

        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'status' => '完了',
        ]);
    }

    public function test_email_is_sent_to_seller_when_buyer_completes(): void
    {
        Mail::fake();
        ['seller' => $seller, 'buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $this->actingAs($buyer)->post('/transaction/' . $purchase->id . '/complete', [
            'rating' => 5,
        ]);

        Mail::assertSent(TransactionCompletedMail::class, function ($mail) use ($seller) {
            return $mail->hasTo($seller->email);
        });
    }

    public function test_cannot_submit_duplicate_rating(): void
    {
        Mail::fake();
        ['seller' => $seller, 'buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        Rating::create([
            'purchase_id' => $purchase->id,
            'rater_user_id' => $buyer->id,
            'rated_user_id' => $seller->id,
            'rating' => 5,
        ]);

        $this->actingAs($buyer)->post('/transaction/' . $purchase->id . '/complete', [
            'rating' => 3,
        ]);

        $this->assertEquals(1, Rating::where('purchase_id', $purchase->id)
            ->where('rater_user_id', $buyer->id)
            ->count());
    }

    public function test_rating_modal_shows_for_seller_when_completed(): void
    {
        ['seller' => $seller, 'buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $purchase->update(['status' => '完了']);

        Rating::create([
            'purchase_id' => $purchase->id,
            'rater_user_id' => $buyer->id,
            'rated_user_id' => $seller->id,
            'rating' => 4,
        ]);

        $response = $this->actingAs($seller)->get('/transaction/' . $purchase->id);

        $response->assertStatus(200);
        $response->assertViewHas('showRatingModal', true);
    }

    public function test_rating_modal_hidden_after_seller_has_rated(): void
    {
        ['seller' => $seller, 'buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $purchase->update(['status' => '完了']);

        Rating::create([
            'purchase_id' => $purchase->id,
            'rater_user_id' => $buyer->id,
            'rated_user_id' => $seller->id,
            'rating' => 4,
        ]);

        Rating::create([
            'purchase_id' => $purchase->id,
            'rater_user_id' => $seller->id,
            'rated_user_id' => $buyer->id,
            'rating' => 3,
        ]);

        $response = $this->actingAs($seller)->get('/transaction/' . $purchase->id);

        $response->assertViewHas('showRatingModal', false);
    }

    public function test_after_rating_redirects_to_items_index(): void
    {
        Mail::fake();
        ['buyer' => $buyer, 'purchase' => $purchase] = $this->createTransaction();

        $response = $this->actingAs($buyer)->post('/transaction/' . $purchase->id . '/complete', [
            'rating' => 5,
        ]);

        $response->assertRedirect('/');
    }

    public function test_average_rating_is_calculated_correctly(): void
    {
        $seller = User::factory()->create(['email_verified_at' => now()]);
        $buyer1 = User::factory()->create(['email_verified_at' => now()]);
        $buyer2 = User::factory()->create(['email_verified_at' => now()]);

        $item1 = Item::factory()->create(['user_id' => $seller->id]);
        $item2 = Item::factory()->create(['user_id' => $seller->id]);

        $purchase1 = Purchase::create([
            'user_id' => $buyer1->id,
            'item_id' => $item1->id,
            'payment_method' => 'コンビニ払い',
            'postal_code' => '123-4567',
            'address' => '東京都',
            'building' => null,
            'status' => '完了',
        ]);

        $purchase2 = Purchase::create([
            'user_id' => $buyer2->id,
            'item_id' => $item2->id,
            'payment_method' => 'コンビニ払い',
            'postal_code' => '123-4567',
            'address' => '東京都',
            'building' => null,
            'status' => '完了',
        ]);

        Rating::create([
            'purchase_id' => $purchase1->id,
            'rater_user_id' => $buyer1->id,
            'rated_user_id' => $seller->id,
            'rating' => 4,
        ]);

        Rating::create([
            'purchase_id' => $purchase2->id,
            'rater_user_id' => $buyer2->id,
            'rated_user_id' => $seller->id,
            'rating' => 5,
        ]);

        $this->assertEquals(5, $seller->fresh()->average_rating);
    }

    public function test_average_rating_is_null_when_no_ratings(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->assertNull($user->average_rating);
    }

    public function test_unauthorized_user_cannot_submit_rating(): void
    {
        ['purchase' => $purchase] = $this->createTransaction();
        $otherUser = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($otherUser)->post('/transaction/' . $purchase->id . '/complete', [
            'rating' => 5,
        ]);

        $response->assertStatus(403);
    }
}
