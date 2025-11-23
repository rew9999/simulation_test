<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Http\Requests\ItemRequest;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Item;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'recommend');
        $keyword = $request->get('keyword');

        $query = Item::query();

        if ($keyword) {
            $query->where('name', 'like', '%'.$keyword.'%');
        }

        if ($tab === 'mylist' && Auth::check()) {
            $likedItemIds = Auth::user()->likes->pluck('item_id');
            $query->whereIn('id', $likedItemIds);
        }

        if (Auth::check()) {
            $query->where('user_id', '!=', Auth::id());
        }

        $items = $query->get();

        return view('items.index', compact('items', 'tab', 'keyword'));
    }

    public function show($id)
    {
        $item = Item::with(['categories', 'comments.user', 'likes'])->findOrFail($id);
        $likesCount = $item->likes->count();
        $commentsCount = $item->comments->count();
        $isLiked = Auth::check() ? $item->likes->contains('user_id', Auth::id()) : false;

        return view('items.show', compact('item', 'likesCount', 'commentsCount', 'isLiked'));
    }

    public function toggleLike($id)
    {
        $item = Item::findOrFail($id);
        $user = Auth::user();

        $like = Like::where('user_id', $user->id)->where('item_id', $id)->first();

        if ($like) {
            $like->delete();
        } else {
            Like::create([
                'user_id' => $user->id,
                'item_id' => $id,
            ]);
        }

        return back();
    }

    public function storeComment(CommentRequest $request, $id)
    {
        Comment::create([
            'user_id' => Auth::id(),
            'item_id' => $id,
            'content' => $request->content,
        ]);

        return back();
    }

    public function create()
    {
        $categories = Category::all();
        $conditions = ['良好', '目立った傷や汚れなし', 'やや傷や汚れあり', '状態が悪い'];

        return view('items.create', compact('categories', 'conditions'));
    }

    public function store(ItemRequest $request)
    {
        $imagePath = $request->file('image')->store('images', 'public');

        $item = Item::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'brand' => $request->brand,
            'description' => $request->description,
            'price' => $request->price,
            'condition' => $request->condition,
            'image' => $imagePath,
        ]);

        $item->categories()->attach($request->categories);

        return redirect()->route('items.index');
    }
}
