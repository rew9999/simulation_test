<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Item;
use App\Models\Like;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_items_index_page(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('items.index');
    }

    public function test_items_index_displays_items_excluding_own_items_for_authenticated_users(): void
    {
        $user = User::factory()->create();
        $ownItem = Item::factory()->create(['user_id' => $user->id]);
        $otherItem = Item::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertDontSee($ownItem->name);
        $response->assertSee($otherItem->name);
    }

    public function test_items_can_be_searched_by_keyword(): void
    {
        $item1 = Item::factory()->create(['name' => 'テスト商品A']);
        $item2 = Item::factory()->create(['name' => '別の商品B']);

        $response = $this->get('/?keyword=テスト');

        $response->assertStatus(200);
        $response->assertSee('テスト商品A');
        $response->assertDontSee('別の商品B');
    }

    public function test_mylist_tab_shows_liked_items_for_authenticated_users(): void
    {
        $user = User::factory()->create();
        $likedItem = Item::factory()->create();
        $notLikedItem = Item::factory()->create();

        Like::create([
            'user_id' => $user->id,
            'item_id' => $likedItem->id,
        ]);

        $response = $this->actingAs($user)->get('/?tab=mylist');

        $response->assertStatus(200);
        $response->assertSee($likedItem->name);
        $response->assertDontSee($notLikedItem->name);
    }

    public function test_authenticated_user_can_view_item_detail_page(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->get('/item/'.$item->id);

        $response->assertStatus(200);
        $response->assertViewIs('items.show');
        $response->assertSee($item->name);
    }

    public function test_guest_can_view_item_detail_page(): void
    {
        $item = Item::factory()->create();

        $response = $this->get('/item/'.$item->id);

        $response->assertStatus(200);
        $response->assertViewIs('items.show');
        $response->assertSee($item->name);
    }

    public function test_authenticated_user_can_like_an_item(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post('/item/'.$item->id.'/like');

        $response->assertStatus(302);
        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    public function test_authenticated_user_can_unlike_an_item(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create();

        Like::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response = $this->actingAs($user)->post('/item/'.$item->id.'/like');

        $response->assertStatus(302);
        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }

    public function test_guest_cannot_like_an_item(): void
    {
        $item = Item::factory()->create();

        $response = $this->post('/item/'.$item->id.'/like');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_post_comment(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post('/item/'.$item->id.'/comment', [
            'content' => 'これはテストコメントです',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'content' => 'これはテストコメントです',
        ]);
    }

    public function test_guest_cannot_post_comment(): void
    {
        $item = Item::factory()->create();

        $response = $this->post('/item/'.$item->id.'/comment', [
            'content' => 'これはテストコメントです',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_comment_cannot_be_empty(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post('/item/'.$item->id.'/comment', [
            'content' => '',
        ]);

        $response->assertSessionHasErrors(['content']);
    }

    public function test_comment_cannot_exceed_255_characters(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post('/item/'.$item->id.'/comment', [
            'content' => str_repeat('あ', 256),
        ]);

        $response->assertSessionHasErrors(['content']);
    }

    public function test_authenticated_user_can_view_sell_page(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->get('/sell');

        $response->assertStatus(200);
        $response->assertViewIs('items.create');
    }

    public function test_guest_cannot_view_sell_page(): void
    {
        $response = $this->get('/sell');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_create_item(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['email_verified_at' => now()]);
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post('/sell', [
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'これはテスト商品の説明です',
            'price' => 10000,
            'condition' => '良好',
            'image' => UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg'),
            'categories' => [$category->id],
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/');

        $this->assertDatabaseHas('items', [
            'user_id' => $user->id,
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'price' => 10000,
        ]);
    }

    public function test_item_name_is_required(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['email_verified_at' => now()]);
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post('/sell', [
            'name' => '',
            'brand' => 'テストブランド',
            'description' => 'これはテスト商品の説明です',
            'price' => 10000,
            'condition' => '良好',
            'image' => UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg'),
            'categories' => [$category->id],
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_item_description_is_required(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['email_verified_at' => now()]);
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post('/sell', [
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => '',
            'price' => 10000,
            'condition' => '良好',
            'image' => UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg'),
            'categories' => [$category->id],
        ]);

        $response->assertSessionHasErrors(['description']);
    }

    public function test_item_price_is_required(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['email_verified_at' => now()]);
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post('/sell', [
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'これはテスト商品の説明です',
            'price' => '',
            'condition' => '良好',
            'image' => UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg'),
            'categories' => [$category->id],
        ]);

        $response->assertSessionHasErrors(['price']);
    }

    public function test_item_price_must_be_numeric(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['email_verified_at' => now()]);
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post('/sell', [
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'これはテスト商品の説明です',
            'price' => 'not-a-number',
            'condition' => '良好',
            'image' => UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg'),
            'categories' => [$category->id],
        ]);

        $response->assertSessionHasErrors(['price']);
    }

    public function test_item_condition_is_required(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['email_verified_at' => now()]);
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post('/sell', [
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'これはテスト商品の説明です',
            'price' => 10000,
            'condition' => '',
            'image' => UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg'),
            'categories' => [$category->id],
        ]);

        $response->assertSessionHasErrors(['condition']);
    }

    public function test_item_image_is_required(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post('/sell', [
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'これはテスト商品の説明です',
            'price' => 10000,
            'condition' => '良好',
            'image' => '',
            'categories' => [$category->id],
        ]);

        $response->assertSessionHasErrors(['image']);
    }

    public function test_item_categories_is_required(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->post('/sell', [
            'name' => 'テスト商品',
            'brand' => 'テストブランド',
            'description' => 'これはテスト商品の説明です',
            'price' => 10000,
            'condition' => '良好',
            'image' => UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg'),
            'categories' => [],
        ]);

        $response->assertSessionHasErrors(['categories']);
    }
}
