<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Http\Requests\PurchaseRequest;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class PurchaseController extends Controller
{
    private const PAYMENT_METHOD_CARD = 'カード支払い';

    private const PAYMENT_METHOD_CONVENIENCE = 'コンビニ払い';

    private const SESSION_ITEM_ID = 'purchase_item_id';

    private const SESSION_PAYMENT_METHOD = 'purchase_payment_method';

    private const SESSION_POSTAL_CODE = 'purchase_postal_code';

    private const SESSION_ADDRESS = 'purchase_address';

    private const SESSION_BUILDING = 'purchase_building';

    private function createPurchase($itemId, $paymentMethod, $postalCode, $address, $building)
    {
        return Purchase::create([
            'user_id' => Auth::id(),
            'item_id' => $itemId,
            'payment_method' => $paymentMethod,
            'postal_code' => $postalCode,
            'address' => $address,
            'building' => $building,
        ]);
    }

    private function clearPurchaseSession()
    {
        session()->forget([
            self::SESSION_ITEM_ID,
            self::SESSION_PAYMENT_METHOD,
            self::SESSION_POSTAL_CODE,
            self::SESSION_ADDRESS,
            self::SESSION_BUILDING,
        ]);
    }

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
            self::SESSION_ITEM_ID => $id,
            self::SESSION_PAYMENT_METHOD => $request->payment_method,
            self::SESSION_POSTAL_CODE => $request->postal_code,
            self::SESSION_ADDRESS => $request->address,
            self::SESSION_BUILDING => $request->building,
        ]);

        if ($request->payment_method === self::PAYMENT_METHOD_CARD) {
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

        $this->createPurchase(
            $id,
            $request->payment_method,
            $request->postal_code,
            $request->address,
            $request->building
        );

        $this->clearPurchaseSession();

        return redirect()->route('items.index')->with('success', '購入が完了しました。');
    }

    public function success($id)
    {
        $item = Item::findOrFail($id);

        if ($item->purchase) {
            return redirect()->route('items.index')->with('error', 'この商品はすでに購入されています。');
        }

        $paymentMethod = session(self::SESSION_PAYMENT_METHOD);
        $postalCode = session(self::SESSION_POSTAL_CODE);
        $address = session(self::SESSION_ADDRESS);
        $building = session(self::SESSION_BUILDING);

        if (! $paymentMethod) {
            return redirect()->route('items.index')->with('error', '購入情報が見つかりません。');
        }

        $this->createPurchase(
            $id,
            $paymentMethod,
            $postalCode,
            $address,
            $building
        );

        $this->clearPurchaseSession();

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
