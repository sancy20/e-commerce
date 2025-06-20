<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use App\Models\Order;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('stripe-signature');
        $endpointSecret = config('cashier.webhook.secret') ?? env('STRIPE_WEBHOOK_SECRET');

        try {
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            Log::error('Stripe Webhook Error: Invalid payload - ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            Log::error('Stripe Webhook Error: Invalid signature - ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        Log::info('Stripe Webhook Event Received: ' . $event->type . ' ID: ' . $event->id);

        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $this->handleCheckoutSessionCompleted($session);
                break;
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                $this->handlePaymentIntentSucceeded($paymentIntent);
                break;
            case 'charge.refunded':
                $charge = $event->data->object;
                $this->handleChargeRefunded($charge);
                break;
            case 'charge.dispute.created':
                $dispute = $event->data->object;
                $this->handleChargeDisputeCreated($dispute);
                break;
            default:
                Log::warning('Stripe Webhook Event: Unhandled event type ' . $event->type);
        }

        return response()->json(['status' => 'success'], 200);
    }

    protected function handleCheckoutSessionCompleted($session)
    {
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
            $order->order_status = 'processing'
            $order->save();
            Log::info('Order ID: ' . $orderId . ' payment status updated to PAID via webhook. Session: ' . $session->id);
        }
    }

    protected function handlePaymentIntentSucceeded($paymentIntent)
    {
        $orderId = $paymentIntent->metadata->order_id ?? null;
        if (!$orderId) {
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

    protected function handleChargeRefunded($charge)
    {
        $orderId = $charge->metadata->order_id ?? null;
        if (!$orderId) {
            Log::warning('Webhook: charge.refunded received without order_id in metadata for charge: ' . $charge->id);
            return;
        }

        $order = Order::find($orderId);
        if (!$order) {
            Log::error('Webhook: order not found for ID: ' . $orderId . ' from refunded charge: ' . $charge->id);
            return;
        }

        if ($charge->amount_refunded === $charge->amount && $order->payment_status !== 'refunded') {
            $order->payment_status = 'refunded';
            $order->save();
            Log::info('Order ID: ' . $orderId . ' payment status updated to REFUNDED via webhook. Charge: ' . $charge->id);
        } else {
            Log::info('Order ID: ' . $orderId . ' received PARTIAL REFUND via webhook. Charge: ' . $charge->id);
        }
    }

    protected function handleChargeDisputeCreated($dispute)
    {
        $orderId = $dispute->metadata->order_id ?? null;
        if (!$orderId) {
            Log::warning('Webhook: charge.dispute.created received without order_id in metadata for dispute: ' . $dispute->id);
            return;
        }

        $order = Order::find($orderId);
        if (!$order) {
            Log::error('Webhook: order not found for ID: ' . $orderId . ' from dispute: ' . $dispute->id);
            return;
        }

        $order->payment_status = 'disputed';
        $order->order_status = 'on_hold';
        $order->save();
        Log::warning('Order ID: ' . $orderId . ' payment status updated to DISPUTED via webhook. Dispute: ' . $dispute->id);
    }
}