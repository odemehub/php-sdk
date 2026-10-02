<?php

declare(strict_types=1);

require_once __DIR__.'/config.php';

use Gurmehub\Odemehub\Exception\SignatureException;
use Gurmehub\Odemehub\Request\RetrieveOrder;
use Gurmehub\Odemehub\Request\RetrievePayment;
use Gurmehub\Odemehub\Request\RetrieveSubscription;

/*
|--------------------------------------------------------------------------
| Webhook
|--------------------------------------------------------------------------
|
| Geçidin kendi sunucusundan gelen bildirimler buraya POST edilir. Adres
| panelde Ayarlar → Webhook sayfasında kanal ve olay seçilerek tanımlanır.
| Gövde olduğu gibi okunur ve imzası gizli anahtarla doğrulanır. Bildirim
| yalnızca neyin değiştiğini token ile söyler; asıl durum geçide sorulur ve
| karar o yanıta göre verilir. 2xx yanıt alana kadar geçit beş kez dener;
| aynı bildirim iki kez gelebilir, `id` ile ayırt edilir.
|
*/

try {
    $webhook = client()->webhook(
        $_SERVER['REQUEST_METHOD'] ?? 'POST',
        parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/',
        (string) file_get_contents('php://input'),
        $_SERVER['HTTP_X_TIMESTAMP'] ?? null,
        $_SERVER['HTTP_X_SIGNATURE'] ?? null,
    );
} catch (SignatureException $exception) {
    http_response_code(401);
    exit($exception->getMessage());
}

$line = match (true) {
    $webhook->orderToken !== null => (function () use ($webhook): string {
        $order = client()->retrieveOrder(new RetrieveOrder($webhook->orderToken))->order;

        return 'sipariş '.$order->channelReference.' -> '.($order->status->value ?? '-').', para '.($order->transaction?->paymentStatus->value ?? '-');
    })(),
    $webhook->subscriptionToken !== null => (function () use ($webhook): string {
        $subscription = client()->retrieveSubscription(new RetrieveSubscription($webhook->subscriptionToken))->subscription;

        return 'abonelik '.$subscription->channelReference.' -> '.($subscription->status->value ?? '-');
    })(),
    $webhook->transactionToken !== null => (function () use ($webhook): string {
        $transaction = client()->retrievePayment(new RetrievePayment($webhook->transactionToken))->transaction;

        return 'ödeme '.$transaction->channelReference.' -> '.($transaction->status->value ?? '-').' / '.($transaction->paymentStatus->value ?? '-');
    })(),
    default => 'bilinmeyen olay',
};

error_log('[odemehub] '.$webhook->id.' '.$webhook->event.' '.$line);

http_response_code(204);
