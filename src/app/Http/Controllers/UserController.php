<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'sell');

        if ($tab === 'sell') {
            $items = $user->items;
        } else {
            $purchasedItemIds = $user->purchases->pluck('item_id');
            $items = \App\Models\Item::whereIn('id', $purchasedItemIds)->get();
        }

        return view('users.show', compact('user', 'items', 'tab'));
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
