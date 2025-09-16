<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
    ];

    /**
     * Get the user that owns the saved item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product that is saved (with brand).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)
                    ->with('brand'); // ⬅️ Ajout pour que le brand soit chargé aussi
    }
}
