<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_purchase_page(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->get('/purchase/'.$item->id);

        $response->assertStatus(200);
        $response->assertViewIs('purchases.show');
        $response->assertSee($item->name);
    }

    public function test_guest_cannot_view_purchase_page(): void
    {
        $item = Item::factory()->create();

        $response = $this->get('/purchase/'.$item->id);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_purchase_item_with_convenience_store_payment(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post('/purchase/'.$item->id, [
            'payment_method' => 'コンビニ払い',
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区道玄坂1-2-3',
            'building' => 'テストビル101',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/');

        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'payment_method' => 'コンビニ払い',
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区道玄坂1-2-3',
            'building' => 'テストビル101',
        ]);
    }

    public function test_payment_method_is_required(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post('/purchase/'.$item->id, [
            'payment_method' => '',
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区道玄坂1-2-3',
            'building' => 'テストビル101',
        ]);

        $response->assertSessionHasErrors(['payment_method']);
    }

    public function test_postal_code_is_required(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post('/purchase/'.$item->id, [
            'payment_method' => 'コンビニ払い',
            'postal_code' => '',
            'address' => '東京都渋谷区道玄坂1-2-3',
            'building' => 'テストビル101',
        ]);

        $response->assertSessionHasErrors(['postal_code']);
    }

    public function test_address_is_required(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post('/purchase/'.$item->id, [
            'payment_method' => 'コンビニ払い',
            'postal_code' => '123-4567',
            'address' => '',
            'building' => 'テストビル101',
        ]);

        $response->assertSessionHasErrors(['address']);
    }

    public function test_building_field_is_optional(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post('/purchase/'.$item->id, [
            'payment_method' => 'コンビニ払い',
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区道玄坂1-2-3',
            'building' => null,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/');

        $this->assertDatabaseHas('purchases', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'building' => null,
        ]);
    }

    public function test_cannot_purchase_already_sold_item(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $buyer = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create();

        Purchase::create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'payment_method' => 'コンビニ払い',
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区道玄坂1-2-3',
            'building' => 'テストビル101',
        ]);

        $response = $this->actingAs($user)->post('/purchase/'.$item->id, [
            'payment_method' => 'コンビニ払い',
            'postal_code' => '123-4567',
            'address' => '東京都渋谷区道玄坂1-2-3',
            'building' => 'テストビル101',
        ]);

        $response->assertSessionHasErrors(['item']);
    }

    public function test_authenticated_user_can_view_address_change_page(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->get('/purchase/address/'.$item->id);

        $response->assertStatus(200);
        $response->assertViewIs('purchases.address');
    }

    public function test_guest_cannot_view_address_change_page(): void
    {
        $item = Item::factory()->create();

        $response = $this->get('/purchase/address/'.$item->id);

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_update_address(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post('/purchase/address/'.$item->id, [
            'postal_code' => '999-8888',
            'address' => '大阪府大阪市北区梅田1-1-1',
            'building' => '梅田ビル5F',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/purchase/'.$item->id);
        $response->assertSessionHas(['postal_code', 'address', 'building']);
    }

    public function test_postal_code_is_required_for_address_update(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post('/purchase/address/'.$item->id, [
            'postal_code' => '',
            'address' => '大阪府大阪市北区梅田1-1-1',
            'building' => '梅田ビル5F',
        ]);

        $response->assertSessionHasErrors(['postal_code']);
    }

    public function test_address_is_required_for_address_update(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post('/purchase/address/'.$item->id, [
            'postal_code' => '999-8888',
            'address' => '',
            'building' => '梅田ビル5F',
        ]);

        $response->assertSessionHasErrors(['address']);
    }

    public function test_building_field_is_optional_for_address_update(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $item = Item::factory()->create();

        $response = $this->actingAs($user)->post('/purchase/address/'.$item->id, [
            'postal_code' => '999-8888',
            'address' => '大阪府大阪市北区梅田1-1-1',
            'building' => null,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/purchase/'.$item->id);
    }
}
