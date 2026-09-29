<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Exception\OdemehubException;
use Gurmehub\Odemehub\Request\RetrievePayment;

/*
|--------------------------------------------------------------------------
| 3D dönüşü ve ödeme sayfası dönüşü
|--------------------------------------------------------------------------
|
| Müşteri bankasından (ya da geçidin ödeme sayfasından) dönünce tarayıcısı
| buraya POST eder. Gelen alanlar sonucu değil, sonucun hazır olduğunu
| söyler: ödemenin numarası, kendi referansınız ve güvenilmez bir ipucu.
| Bunları gönderen bizim sunucumuz değil, müşterinin tarayıcısı olduğu için
| imzalanamazlar; sonucu geçide kendiniz sorarsınız ve o yanıt imzalıdır.
|
*/

pageStart('Ödeme sonucu');

$transactionToken = (string) ($_POST['transaction_token'] ?? '');

if ($transactionToken === '') {
    notice("Dönüşte işlem token'ı yok.");
} else {
    try {
        paymentResult(client()->retrievePayment(new RetrievePayment(transactionToken: $transactionToken)));
    } catch (OdemehubException $exception) {
        notice($exception->getMessage());
    }
}

echo '<h2>Dönüşte gelen alanlar</h2>';
echo '<pre>'.e(print_r($_POST, true)).'</pre>';

pageEnd();
