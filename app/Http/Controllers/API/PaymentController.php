<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Events\PaymentReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/payments/process",
     *     summary="Process payment for an order",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"order_id","payment_method"},
     *             @OA\Property(property="order_id", type="integer", example=1),
     *             @OA\Property(property="payment_method", type="string", example="mtn"),
     *             @OA\Property(property="phone_number", type="string", example="237612345678"),
     *             @OA\Property(property="card_number", type="string", example="4242424242424242"),
     *             @OA\Property(property="expiry_month", type="string", example="12"),
     *             @OA\Property(property="expiry_year", type="string", example="2025"),
     *             @OA\Property(property="cvc", type="string", example="123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment processed successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function processPayment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|exists:orders,id',
                'payment_method' => 'required|string|in:mtn,orange,paypal,stripe',
                'phone_number' => 'required_if:payment_method,mtn,orange|string',
                'card_number' => 'required_if:payment_method,paypal,stripe|string',
                'expiry_month' => 'required_if:payment_method,paypal,stripe|string',
                'expiry_year' => 'required_if:payment_method,paypal,stripe|string',
                'cvc' => 'required_if:payment_method,paypal,stripe|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = $request->user();
            $order = Order::find($request->order_id);

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            // Vérifier si l'utilisateur est autorisé à payer cette commande
            if (!$user->isAdmin() && $order->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Vérifier si la commande est en attente de paiement
            if ($order->status !== 'pending') {
                return response()->json(['message' => 'This order cannot be paid. Status: ' . $order->status], 422);
            }

            // Traiter le paiement en fonction de la méthode
            $paymentResult = $this->handlePaymentMethod($request, $order);

            if (!$paymentResult['success']) {
                return response()->json(['message' => $paymentResult['message']], 422);
            }

            // Créer l'enregistrement de paiement
            $payment = new Payment();
            $payment->order_id = $order->id;
            $payment->payment_method = $request->payment_method;
            $payment->amount = $order->total;
            $payment->transaction_id = $paymentResult['transaction_id'];
            $payment->status = 'completed';
            $payment->save();

            // Mettre à jour le statut de la commande
            $order->status = 'paid';
            $order->save();

            // Déclencher l'événement de paiement reçu
            event(new PaymentReceived($payment));

            return response()->json([
                'message' => 'Payment processed successfully',
                'payment' => $payment,
                'order' => $order
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Payment processing failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle different payment methods
     */
    private function handlePaymentMethod(Request $request, Order $order)
    {
        $transactionId = 'txn_' . Str::random(16);

        switch ($request->payment_method) {
            case 'mtn':
                // Simuler un paiement MTN Mobile Money
                // Dans un environnement réel, vous intégreriez l'API MTN ici
                return [
                    'success' => true,
                    'message' => 'MTN Mobile Money payment successful',
                    'transaction_id' => 'mtn_' . $transactionId
                ];

            case 'orange':
                // Simuler un paiement Orange Money
                // Dans un environnement réel, vous intégreriez l'API Orange Money ici
                return [
                    'success' => true,
                    'message' => 'Orange Money payment successful',
                    'transaction_id' => 'orange_' . $transactionId
                ];

            case 'paypal':
                // Simuler un paiement PayPal
                // Dans un environnement réel, vous intégreriez l'API PayPal ici
                return [
                    'success' => true,
                    'message' => 'PayPal payment successful',
                    'transaction_id' => 'paypal_' . $transactionId
                ];

            case 'stripe':
                // Simuler un paiement Stripe
                // Dans un environnement réel, vous intégreriez l'API Stripe ici
                return [
                    'success' => true,
                    'message' => 'Stripe payment successful',
                    'transaction_id' => 'stripe_' . $transactionId
                ];

            default:
                return [
                    'success' => false,
                    'message' => 'Unsupported payment method'
                ];
        }
    }

    /**
     * @OA\Get(
     *     path="/api/payments/history",
     *     summary="Get payment history",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Payment history",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Payment")
     *         )
     *     )
     * )
     */
    public function getPaymentHistory(Request $request)
    {
        $user = $request->user();

        // Si l'utilisateur est admin, retourner tous les paiements
        if ($user->isAdmin()) {
            $payments = Payment::with('order')->get();
        } else {
            // Sinon, retourner uniquement les paiements de l'utilisateur
            $payments = Payment::with('order')
                ->whereHas('order', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->get();
        }

        return response()->json($payments);
    }

    /**
     * @OA\Get(
     *     path="/api/payments/user/{user_id}",
     *     summary="Get payment history for a specific user",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="user_id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment history for the specified user",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Payment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized access"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function getUserPaymentHistory(Request $request, $user_id)
    {
        // Vérifier si l'utilisateur authentifié est admin ou s'il demande son propre historique
        $currentUser = $request->user();

        if (!$currentUser->isAdmin() && $currentUser->id != $user_id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        // Vérifier si l'utilisateur existe
        $user = \App\Models\User::find($user_id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Récupérer les paiements de l'utilisateur
        $payments = Payment::with('order')
            ->whereHas('order', function($query) use ($user_id) {
                $query->where('user_id', $user_id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($payments);
    }
}
