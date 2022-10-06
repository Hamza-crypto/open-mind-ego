<?php
// webhook.php
//
// Use this sample code to handle webhook events in your integration.
//
// 1) Paste this code into a new file (webhook.php)
//
// 2) Install dependencies
//   composer require stripe/stripe-php
//
// 3) Run the server on http://localhost:4242 (or anywhere else)
//   php -S localhost:4242

require_once('wp-load.php');
require_once get_home_path() . "vendor/autoload.php";

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// This is your Stripe CLI webhook secret for testing your endpoint locally.
$endpoint_secret = 'whsec_5b4c93691dd7f12d2db302c2a8f383d32fa3d21c732f4ab90c488680ee2de1dc';

$log = new Logger('stripe_webhook');
$log->pushHandler(new StreamHandler('stripe_webhook.log', 'info'));

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

$event = null;

try {

    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );
} catch(\UnexpectedValueException $e) {
    // Invalid payload
    http_response_code(400);
    exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    http_response_code(400);
    exit();
}


// Handle the event
switch ($event->type) {    case 'charge.succeeded':
        $paymentIntent = $event->data->object->metadata;

        $user_id = (int)$paymentIntent['user_id'];
        $level_id = (int)$paymentIntent['level_id'];

        if($level_id == 4){ // Cappuccino coffee
            update_user_meta( $user_id, 'number_of_messages_left', 4);
            $log->info('update', ['user_id' => $user_id, 'level_id' => $level_id]);
        }
        elseif($level_id == 3){ // Coffee express
            update_user_meta( $user_id, 'number_of_messages_left', 3);
            $log->info('update', ['user_id' => $user_id, 'level_id' => $level_id]);

        }elseif($level_id == 2){ // Just coffee
            update_user_meta( $user_id, 'number_of_messages_left', 2);
            $log->info('update', ['user_id' => $user_id, 'level_id' => $level_id]);
        }

    default:
        echo 'Received unknown event type ' . $event->type;
}



http_response_code(200);





/**
 * STRIPE CLI EVENT TRIGGER
 * stripe trigger charge.succeeded --add charge:metadata[user_id]="36" --add charge:metadata[level_id]="4"
 */