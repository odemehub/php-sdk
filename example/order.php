<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Request\CreateOrder;
use Gurmehub\Odemehub\Request\RetrieveOrders;

/*
|--------------------------------------------------------------------------
| Sipariş
|--------------------------------------------------------------------------
|
| Kart sorulmaz: sipariş açılır ve geçit, müşterinin kartını gireceği kendi
| sayfasının adresini (checkout_url) döndürür. Müşteri oraya yönlendirilir,
| ödemeyi orada yapar ve tarayıcısı `success_url` adresine POST edilir —
| 3D dönüşüyle birebir aynı biçimde, yani return.php onu da okur.
|
| Tutar gönderilmez; geçit kalemleri ve ödeyenin panelinizdeki listeden seçtiği
| gönderim yöntemini toplar.
| Müşterinin bilinen kısmı gönderilir, kalanı sayfada sorulur. Aynı referansla
| ikinci kez açmak açık siparişi günceller; ödenmiş sipariş değişmez.
|
*/

$message = null;

/** @var array<string, list<string>> $errors */
$errors = [];

$order = null;
$found = null;

if (isSubmitted() && posted('action') === 'retrieve') {
    $found = attempt(fn () => client()->retrieveOrders(RetrieveOrders::byToken(posted('token'))), $message, $errors)?->orders[0] ?? null;
    $message ??= $found === null ? 'Bu token ile sipariş bulunamadı.' : null;
}

if (isSubmitted() && posted('action') === 'create') {
    $order = attempt(fn () => client()->createOrder(new CreateOrder(
        reference: posted('reference'),
        successUrl: posted('success_url'),
        items: [postedItem()],
        customer: postedCustomer(),
        cancelUrl: 'http://localhost:8080/index.php',
        description: postedOrNull('description'),
        requiresShipping: posted('requires_shipping') !== '',
    )), $message, $errors);
}

pageStart('Sipariş');

notice($message);

if ($order !== null) {
    notice($order->result->message ?? 'Sipariş hazır.', $order->result->successful);
    checkoutResult($order->order);
    echo '<div class="actions"><a class="button" href="order.php">Yeni sipariş</a><a class="button" href="index.php">Başa dön</a></div>';
} elseif ($found !== null) {
    checkoutResult($found);
    echo '<div class="actions"><a class="button" href="order.php">Yeni sipariş</a><a class="button" href="index.php">Başa dön</a></div>';
} else {
    form([
        'Sipariş' => [
            'reference' => 'Sipariş referansı (sizdeki)',
            'description' => 'Açıklama',
            'success_url' => 'Başarı adresi',
            'requires_shipping' => 'Gönderim (dolu: adres ve gönderim yöntemi sorulur)',
        ],
        'Kalem' => itemFields(),
        'Müşteri' => customerFields(),
    ], $errors, 'Ödeme sayfasını aç', ['description', 'requires_shipping', ...array_keys(customerFields())], 'order', ['action' => 'create']);

    echo '<form method="post"><input type="hidden" name="action" value="retrieve">';
    echo '<h2>Var olan siparişi getir</h2><div class="grid"><div'.(isset($errors['token']) ? ' class="invalid"' : '').'>';
    echo '<label for="token">Sipariş token</label><input id="token" name="token" value="'.e(posted('token')).'">';
    echo '</div></div><div class="actions"><button class="button" type="submit">Getir</button></div></form>';
}

pageEnd();
