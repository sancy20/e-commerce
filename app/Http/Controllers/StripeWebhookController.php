<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // For logging webhook events
use Stripe\Webhook; // Import Stripe Webhook class
use Stripe\Exception\SignatureVerificationException; // For signature verification
use App\Models\Order; // To update order status

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('stripe-signature');
        $endpointSecret = config('cashier.webhook.secret') ?? env('STRIPE_WEBHOOK_SECRET'); // Get secret from config or env

        try {
            // Verify the webhook signature to ensure it's from Stripe
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            Log::error('Stripe Webhook Error: Invalid payload - ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            Log::error('Stripe Webhook Error: Invalid signature - ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Log the event for debugging (useful to see all incoming events)
        Log::info('Stripe Webhook Event Received: ' . $event->type . ' ID: ' . $event->id);

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object; // contains a Stripe\Checkout\Session
                $this->handleCheckoutSessionCompleted($session);
                break;
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object; // contains a Stripe\PaymentIntent
                $this->handlePaymentIntentSucceeded($paymentIntent);
                break;
            case 'charge.refunded':
                $charge = $event->data->object; // contains a Stripe\Charge
                $this->handleChargeRefunded($charge);
                break;
            case 'charge.dispute.created':
                $dispute = $event->data->object; // contains a Stripe\Dispute
                $this->handleChargeDisputeCreated($dispute);
                break;
            // ... handle other event types as needed
            default:
                Log::warning('Stripe Webhook Event: Unhandled event type ' . $event->type);
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Handle checkout.session.completed event (primary for confirming payments from Checkout).
     */
    protected function handleCheckoutSessionCompleted($session)
    {
        // Retrieve your order using a metadata field or client_reference_id
        // Assumes you stored your order ID in session.metadata['order_id'] during CheckoutController
        // or in PaymentIntent metadata.
        $orderId = $session->metadata->order_id ?? null;
        if (!$orderId) {
            Log::error('Webhook: checkout.session.completed received without order_id in metadata for session: ' . $session->id);
            return;
        }

        $order = Order::find($orderId);
        if (!$order) {
            Log::error('Webhook: order not found for ID: ' . $orderId . ' from session: ' . $session->id);
            return;
        }

        if ($session->payment_status === 'paid' && $order->payment_status !== 'paid') {
            $order->payment_status = 'paid';
            $order->order_status = 'processing'; // Change order status to processing
            $order->save();
            Log::info('Order ID: ' . $orderId . ' payment status updated to PAID via webhook. Session: ' . $session->id);

            // Additional logic: dispatch email notification to admin/vendor (if not already done at checkout)
        }
    }

    /**
     * Handle payment_intent.succeeded event (alternative for confirming payment, more generic).
     */
    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        $orderId = $paymentIntent->metadata->order_id ?? null; // Assumes order_id stored in PaymentIntent metadata
        if (!$orderId) {
            // If order ID not in metadata, try to find by payment_intent_id if you store it
            // Or handle based on other unique identifiers.
            Log::warning('Webhook: payment_intent.succeeded received without order_id in metadata for PI: ' . $paymentIntent->id);
            return;
        }

        $order = Order::find($orderId);
        if (!$order) {
            Log::error('Webhook: order not found for ID: ' . $orderId . ' from PI: ' . $paymentIntent->id);
            return;
        }

        if ($order->payment_status !== 'paid') {
            $order->payment_status = 'paid';
            $order->order_status = 'processing';
            $order->save();
            Log::info('Order ID: ' . $orderId . ' payment status updated to PAID via webhook. PI: ' . $paymentIntent->id);
        }
    }

    /**
     * Handle charge.refunded event.
     */
    protected function handleChargeRefunded($charge)
    {
        $orderId = $charge->metadata->order_id ?? null; // Assumes order_id stored in charge metadata
        if (!$orderId) {
            Log::warning('Webhook: charge.refunded received without order_id in metadata for charge: ' . $charge->id);
            return;
        }

        $order = Order::find($orderId);
        if (!$order) {
            Log::error('Webhook: order not found for ID: ' . $orderId . ' from refunded charge: ' . $charge->id);
            return;
        }

        // Check if it's a full refund or partial
        if ($charge->amount_refunded === $charge->amount && $order->payment_status !== 'refunded') {
            $order->payment_status = 'refunded';
            // Potentially update order_status to 'cancelled' or similar if fully refunded
            $order->save();
            Log::info('Order ID: ' . $orderId . ' payment status updated to REFUNDED via webhook. Charge: ' . $charge->id);
        } else {
            // Handle partial refunds if necessary
            Log::info('Order ID: ' . $orderId . ' received PARTIAL REFUND via webhook. Charge: ' . $charge->id);
        }
    }

    /**
     * Handle charge.dispute.created event.
     */
    protected function handleChargeDisputeCreated($dispute)
    {
        $orderId = $dispute->metadata->order_id ?? null; // Assumes order_id stored in dispute metadata
        if (!$orderId) {
            Log::warning('Webhook: charge.dispute.created received without order_id in metadata for dispute: ' . $dispute->id);
            return;
        }

        $order = Order::find($orderId);
        if (!$order) {
            Log::error('Webhook: order not found for ID: ' . $orderId . ' from dispute: ' . $dispute->id);
            return;
        }

        // Update order status to reflect a dispute
        $order->payment_status = 'disputed';
        $order->order_status = 'on_hold'; // Or a specific 'disputed' status
        $order->save();
        Log::warning('Order ID: ' . $orderId . ' payment status updated to DISPUTED via webhook. Dispute: ' . $dispute->id);

        // Notify admin
        // Mail::to('admin@example.com')->send(new DisputeNotificationMail($order, $dispute));
    }
}