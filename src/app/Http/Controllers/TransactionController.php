<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatMessageRequest;
use App\Http\Requests\RatingRequest;
use App\Mail\TransactionCompletedMail;
use App\Models\Message;
use App\Models\Purchase;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    private function authorizeTransaction(Purchase $purchase): void
    {
        $userId = Auth::id();
        if ($purchase->user_id !== $userId && $purchase->item->user_id !== $userId) {
            abort(403);
        }
    }

    public function show($purchaseId)
    {
        $purchase = Purchase::with(['item.user', 'messages.user', 'ratings'])->findOrFail($purchaseId);
        $this->authorizeTransaction($purchase);

        $user = Auth::user();
        $isBuyer = $purchase->user_id === $user->id;
        $otherUser = $isBuyer ? $purchase->item->user : $purchase->user;

        $purchase->messages()
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $otherPurchases = Purchase::where(function ($query) use ($user) {
            $query->where('status', '取引中')
                ->orWhere(function ($q) use ($user) {
                    $q->where('status', '完了')
                        ->whereDoesntHave('ratings', function ($r) use ($user) {
                            $r->where('rater_user_id', $user->id);
                        });
                });
        })
            ->where('id', '!=', $purchase->id)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhereHas('item', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->with('item')
            ->get();

        $messages = $purchase->messages()->with('user')->orderBy('created_at', 'asc')->get();

        $showRatingModal = false;
        if ($purchase->isCompleted()) {
            $hasRated = Rating::where('purchase_id', $purchase->id)
                ->where('rater_user_id', $user->id)
                ->exists();
            if (! $hasRated) {
                $showRatingModal = true;
            }
        }

        $draft = session('chat_draft_'.$purchase->id, '');

        return view('transactions.chat', compact(
            'purchase',
            'otherUser',
            'isBuyer',
            'messages',
            'otherPurchases',
            'showRatingModal',
            'draft'
        ));
    }

    public function storeMessage(ChatMessageRequest $request, $purchaseId)
    {
        $purchase = Purchase::with('item')->findOrFail($purchaseId);
        $this->authorizeTransaction($purchase);

        $data = [
            'user_id' => Auth::id(),
            'purchase_id' => $purchase->id,
            'content' => $request->content,
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('messages', 'public');
        }

        Message::create($data);

        session()->forget('chat_draft_'.$purchase->id);

        return redirect()->route('transaction.show', $purchase->id);
    }

    public function updateMessage(ChatMessageRequest $request, $messageId)
    {
        $message = Message::findOrFail($messageId);

        if ($message->user_id !== Auth::id()) {
            abort(403);
        }

        $message->content = $request->content;

        if ($request->hasFile('image')) {
            if ($message->image) {
                Storage::disk('public')->delete($message->image);
            }
            $message->image = $request->file('image')->store('messages', 'public');
        }

        $message->save();

        return redirect()->route('transaction.show', $message->purchase_id);
    }

    public function deleteMessage($messageId)
    {
        $message = Message::findOrFail($messageId);

        if ($message->user_id !== Auth::id()) {
            abort(403);
        }

        $purchaseId = $message->purchase_id;

        if ($message->image) {
            Storage::disk('public')->delete($message->image);
        }

        $message->delete();

        return redirect()->route('transaction.show', $purchaseId);
    }

    public function storeRating(RatingRequest $request, $purchaseId)
    {
        $purchase = Purchase::with('item.user')->findOrFail($purchaseId);
        $this->authorizeTransaction($purchase);

        $user = Auth::user();
        $isBuyer = $purchase->user_id === $user->id;

        $ratedUser = $isBuyer ? $purchase->item->user : $purchase->user;

        $alreadyRated = Rating::where('purchase_id', $purchase->id)
            ->where('rater_user_id', $user->id)
            ->exists();

        if ($alreadyRated) {
            return redirect()->route('items.index');
        }

        Rating::create([
            'purchase_id' => $purchase->id,
            'rater_user_id' => $user->id,
            'rated_user_id' => $ratedUser->id,
            'rating' => $request->rating,
        ]);

        if ($isBuyer && $purchase->isInTransaction()) {
            $purchase->status = '完了';
            $purchase->save();

            Mail::to($purchase->item->user->email)
                ->send(new TransactionCompletedMail($purchase, $user));
        }

        return redirect()->route('items.index');
    }

    public function saveDraft(Request $request, $purchaseId)
    {
        session(['chat_draft_'.$purchaseId => $request->input('content', '')]);

        return response()->json(['status' => 'ok']);
    }
}
