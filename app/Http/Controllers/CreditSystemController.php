<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Stripe\Stripe;
use Stripe\PaymentMethod;
use Stripe\Customer;
use Stripe\Checkout\Session;

class CreditSystemController extends BaseController
{    
    /**
     * Purchase Credit
     *
     * @param  mixed $request
     * @return void
     */
    public function purchaseCredit(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        Stripe::setApiKey(config('stripe.STRIPE_SECRET'));

        if (!$user->stripe_customer_id) {
            $customer = \Stripe\Customer::create([
                'email' => $user->email,
                'name' => $user->name,
            ]);

            $user->stripe_customer_id = $customer->id;
            $user->save();
        }

        $checkoutSession = Session::create([
            'payment_method_types' => ['card'],
            'customer' => $user->stripe_customer_id,
            'line_items' => [[
                'price_data' => [
                    'currency' => config('stripe.CASHIER_CURRENCY'),
                    'product_data' => [
                        'name' => 'Purchase Credits',
                    ],
                    'unit_amount' => $request->amount * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => url('/payment-success?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => url('/payment-failed'),
        ]);

        return $this->sendResponse($checkoutSession->url, '');
    }
    
    /**
     * get Credit
     *
     * @return void
     */
    public function getCredit()
    {
        $user = Auth::user();
        return $this->sendResponse($user->credits, "Get Credits successfully.");
    }
}
