<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Charge;

class PaymentController extends Controller
{
    // Display the payment page
    public function showPaymentPage()
    {
        return view('pages/payment');
    }

    // Handle payment processing
    public function processPayment(Request $request)
{
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $charge = \Stripe\Charge::create([
                'amount' => $request->amount * 100, // Amount in cents
                'currency' => 'eur',
                'source' => $request->stripeToken, // Token from Stripe.js
                'description' => 'Payment Description',
            ]);

            $user = auth()->user();

            $user->credit += $request->amount;

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment successful!',
                'charge_id' => $charge->id,
                'charge_amount' => $request->amount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

}

?>