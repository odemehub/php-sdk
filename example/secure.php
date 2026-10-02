<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Request\SecurePayment;

/*
|--------------------------------------------------------------------------
| 3D Secure ödeme
|--------------------------------------------------------------------------
|
| Ödeme geçidi bankanın istediği formu kendisi saklar ve kendisi sunar; bize
| yalnızca müşteriyi göndereceğimiz adres döner. Ödeme başlamışsa müşteri
| oraya yönlendirilir; bankasında işini bitirince tarayıcısı formdaki dönüş
| adresine POST edilir ve sonuç orada geçide sorulur (return.php).
|
*/

$message = null;

/** @var array<string, list<string>> $errors */
$errors = [];

if (isSubmitted()) {
    $payment = attempt(fn () => client()->securePayment(new SecurePayment(
        ...postedPayment(),
        callbackUrl: posted('callback_url'),
    )), $message, $errors);

    if ($payment !== null && $payment->redirectUrl !== null) {
        header('Location: '.$payment->redirectUrl);

        exit;
    }

    $message ??= $payment?->result->message;
}

pageStart('3D Secure ödeme');

notice($message);
paymentForm($errors, ['Dönüş' => ['callback_url' => 'Dönüş adresi']]);

pageEnd();
