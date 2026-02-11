<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'rater_user_id',
        'rated_user_id',
        'rating',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function rater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rater_user_id');
    }

    public function rated(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rated_user_id');
    }
}
