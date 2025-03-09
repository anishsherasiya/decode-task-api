<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Purchase;
use Stripe\Webhook;

class WebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = @file_get_contents('php://input');
        $endpoint_secret = config('stripe.STRIPE_WEBHOOK_SECRET');

        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        try {
            $event = Webhook::constructEvent(
                json_encode($payload),
                $sig_header,
                $endpoint_secret
            );
        } catch (\Exception $e) {
            Log::error('Stripe Webhook Error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook signature verification failed'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data['object'];

            $user = User::where('stripe_customer_id', $session['customer'])->first();
            if ($user) {
                $amount = $session['amount_total'];
                $credits = $amount / 100;

                $user->increment('credits', $credits);

                Purchase::create([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'type' => 'credit',
                    'stripe_payment_id' => $session['id']
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }
}
