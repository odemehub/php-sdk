<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Request\RetrievePayment;

/*
|--------------------------------------------------------------------------
| 3D dönüşü ve ödeme sayfası dönüşü
|--------------------------------------------------------------------------
|
| Müşteri bankasından (ya da geçidin ödeme sayfasından) dönünce tarayıcısı
| buraya POST eder. Gelen alanlar sonucu değil, sonucun hazır olduğunu
| söyler: ödemenin token'ı, kendi referansınız ve güvenilmez bir ipucu.
| Bunları gönderen bizim sunucumuz değil, müşterinin tarayıcısı olduğu için
| imzalanamazlar; sonucu geçide kendiniz sorarsınız ve o yanıt imzalıdır.
|
*/

$message = null;

/** @var array<string, list<string>> $errors */
$errors = [];

$transactionToken = posted('transaction_token');

pageStart('Ödeme sonucu');

if ($transactionToken === '') {
    notice("Dönüşte işlem token'ı yok.");
} else {
    $payment = attempt(fn () => client()->retrievePayment(new RetrievePayment($transactionToken)), $message, $errors);

    if ($payment !== null) {
        paymentResult($payment);
    } else {
        notice($message);
    }
}

echo '<h2>Dönüşte gelen alanlar</h2>';
echo '<pre>'.e(print_r($_POST, true)).'</pre>';

pageEnd();
