<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="Payment",
 *     required={"id", "order_id", "payment_method", "amount", "transaction_id", "status"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_id", type="integer", example=1),
 *     @OA\Property(property="payment_method", type="string", enum={"mtn", "orange", "paypal", "stripe"}, example="mtn"),
 *     @OA\Property(property="amount", type="number", format="float", example=1299.99),
 *     @OA\Property(property="transaction_id", type="string", example="mtn_txn_123456789"),
 *     @OA\Property(property="status", type="string", enum={"pending", "completed", "failed"}, example="completed"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="PaymentRequest",
 *     required={"order_id", "payment_method", "transaction_id"},
 *     @OA\Property(property="order_id", type="integer", example=1),
 *     @OA\Property(property="payment_method", type="string", enum={"mtn", "orange", "paypal", "stripe"}, example="mtn"),
 *     @OA\Property(property="transaction_id", type="string", example="mtn_txn_123456789")
 * )
 */
class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'order_id',
        'payment_method',
        'payment_details',
        'amount',
        'transaction_id',
        'status',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the order that owns the payment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * Get the user that owns the payment method.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
