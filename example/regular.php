<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Request\RegularPayment;

/*
|--------------------------------------------------------------------------
| Doğrudan ödeme
|--------------------------------------------------------------------------
|
| Kart doğrudan çekilir; müşteri hiçbir yere gitmez ve başarılı yanıt tahsil
| edilmiş ödeme demektir. Reddedilen ödeme de bir sonuçtur ve aynı biçimde
| döner; geçit isteğin kendisini reddederse form, hatalarıyla birlikte geri
| gelir.
|
*/

$message = null;

/** @var array<string, list<string>> $errors */
$errors = [];

$payment = isSubmitted()
    ? attempt(fn () => client()->regularPayment(new RegularPayment(...postedPayment())), $message, $errors)
    : null;

pageStart('Doğrudan ödeme');

if ($payment !== null) {
    paymentResult($payment);
} else {
    notice($message);
    paymentForm($errors);
}

pageEnd();
