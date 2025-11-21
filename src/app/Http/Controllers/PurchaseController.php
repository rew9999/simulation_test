<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Http\Requests\PurchaseRequest;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class PurchaseController extends Controller
{
    public function show($id)
    {
        $item = Item::findOrFail($id);
        $user = Auth::user();

        return view('purchases.show', compact('item', 'user'));
    }

    public function store(PurchaseRequest $request, $id)
    {
        $item = Item::findOrFail($id);

        if ($item->purchase) {
            return back()->withErrors(['item' => 'この商品はすでに購入されています。']);
        }

        session([
            'purchase_item_id' => $id,
            'purchase_payment_method' => $request->payment_method,
            'purchase_postal_code' => $request->postal_code,
            'purchase_address' => $request->address,
            'purchase_building' => $request->building,
        ]);

        if ($request->payment_method === 'カード支払い') {
            Stripe::setApiKey(config('stripe.secret'));

            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'jpy',
                        'product_data' => [
                            'name' => $item->name,
                        ],
                        'unit_amount' => $item->price,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('purchase.success', $id),
                'cancel_url' => route('purchase.show', $id),
            ]);

            return redirect($session->url);
        }

        Purchase::create([
            'user_id' => Auth::id(),
            'item_id' => $id,
            'payment_method' => $request->payment_method,
            'postal_code' => $request->postal_code,
            'address' => $request->address,
            'building' => $request->building,
        ]);

        session()->forget(['purchase_item_id', 'purchase_payment_method', 'purchase_postal_code', 'purchase_address', 'purchase_building']);

        return redirect()->route('items.index')->with('success', '購入が完了しました。');
    }

    public function success($id)
    {
        $item = Item::findOrFail($id);

        if ($item->purchase) {
            return redirect()->route('items.index')->with('error', 'この商品はすでに購入されています。');
        }

        $paymentMethod = session('purchase_payment_method');
        $postalCode = session('purchase_postal_code');
        $address = session('purchase_address');
        $building = session('purchase_building');

        if (!$paymentMethod) {
            return redirect()->route('items.index')->with('error', '購入情報が見つかりません。');
        }

        Purchase::create([
            'user_id' => Auth::id(),
            'item_id' => $id,
            'payment_method' => $paymentMethod,
            'postal_code' => $postalCode,
            'address' => $address,
            'building' => $building,
        ]);

        session()->forget(['purchase_item_id', 'purchase_payment_method', 'purchase_postal_code', 'purchase_address', 'purchase_building']);

        return redirect()->route('items.index')->with('success', '購入が完了しました。');
    }

    public function editAddress($id)
    {
        $item = Item::findOrFail($id);
        $user = Auth::user();

        return view('purchases.address', compact('item', 'user'));
    }

    public function updateAddress(AddressRequest $request, $id)
    {
        return redirect()->route('purchase.show', $id)->with([
            'postal_code' => $request->postal_code,
            'address' => $request->address,
            'building' => $request->building,
        ]);
    }
}
