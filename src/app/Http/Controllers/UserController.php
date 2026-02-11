<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\Item;
use App\Models\Message;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'sell');

        $totalUnread = Message::where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->whereHas('purchase', function ($query) use ($user) {
                $query->where('status', '取引中')
                    ->where(function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                            ->orWhereHas('item', function ($q2) use ($user) {
                                $q2->where('user_id', $user->id);
                            });
                    });
            })
            ->count();

        if ($tab === 'sell') {
            $items = $user->items;

            return view('users.show', compact('user', 'items', 'tab', 'totalUnread'));
        }

        if ($tab === 'buy') {
            $purchasedItemIds = $user->purchases->pluck('item_id');
            $items = Item::whereIn('id', $purchasedItemIds)->get();

            return view('users.show', compact('user', 'items', 'tab', 'totalUnread'));
        }

        $purchases = Purchase::where('status', '取引中')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('item', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->with(['item', 'messages'])
            ->get();

        $purchases = $purchases->sortByDesc(function ($purchase) {
            $latestMessage = $purchase->messages->sortByDesc('created_at')->first();

            return $latestMessage ? $latestMessage->created_at : $purchase->created_at;
        });

        $unreadCounts = [];
        foreach ($purchases as $purchase) {
            $unreadCounts[$purchase->id] = $purchase->messages
                ->where('user_id', '!=', $user->id)
                ->where('is_read', false)
                ->count();
        }

        $items = collect();

        return view('users.show', compact('user', 'items', 'tab', 'totalUnread', 'purchases', 'unreadCounts'));
    }

    public function edit()
    {
        $user = Auth::user();

        return view('users.edit', compact('user'));
    }

    public function update(ProfileRequest $request)
    {
        $user = Auth::user();
        $user->name = $request->name;
        $user->postal_code = $request->postal_code;
        $user->address = $request->address;
        $user->building = $request->building;

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profiles', 'public');
            $user->profile_image = $path;
        }

        $user->save();

        return redirect()->route('mypage')->with('success', 'プロフィールを更新しました。');
    }
}
