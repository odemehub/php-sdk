<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Enum\Currency;
use Gurmehub\Odemehub\Request\CreatePaymentLink;
use Gurmehub\Odemehub\Request\RetrievePaymentLinks;
use Gurmehub\Odemehub\Request\UpdatePaymentLink;

/*
|--------------------------------------------------------------------------
| Ödeme linki
|--------------------------------------------------------------------------
|
| Link açılır ve geçit, kim açarsa onun ödeyebileceği sayfanın adresini
| (checkout_url) döndürür. Müşteri yoktur; ödeyen sayfada kendini söyler.
| Her create-payment-link çağrısı, referans daha önce gönderilmiş olsa da
| yeni bir link ve yeni bir token açar; linki sonradan token'ıyla
| güncellersiniz. Linkteki denemeler retrieve-payment-links ile (son 50
| deneme ve transactions_count), kimlerin ne ödediği — tutar, kalemler,
| ödeyenin fatura adresi — retrieve-link-payments ile görülür. Kapatmak
| update-payment-link ile `is_active: false` göndermektir. checkout_url
| yalnızca link ödenebilirken doludur.
|
*/

$message = null;

/** @var array<string, list<string>> $errors */
$errors = [];

$link = match (isSubmitted() ? posted('action') : null) {
    'create' => attempt(fn () => client()->createPaymentLink(new CreatePaymentLink(
        items: [postedItem()],
        currency: Currency::from(posted('currency')),
        reference: postedOrNull('reference'),
        description: postedOrNull('description'),
        expiresAt: postedOrNull('expires_at'),
    )), $message, $errors),
    'deactivate' => attempt(fn () => client()->updatePaymentLink(new UpdatePaymentLink(token: posted('token'), isActive: false)), $message, $errors),
    default => null,
};

$found = isSubmitted() && posted('action') === 'retrieve'
    ? attempt(fn () => client()->retrievePaymentLinks(RetrievePaymentLinks::byToken(posted('token'))), $message, $errors)?->paymentLinks[0] ?? null
    : null;

if (isSubmitted() && posted('action') === 'retrieve' && $found === null) {
    $message ??= 'Bu token ile ödeme linki bulunamadı.';
}

pageStart('Ödeme linki');

notice($message);

$shown = $found ?? $link?->paymentLink;

if ($shown !== null) {
    if ($link !== null) {
        notice($link->result->message ?? 'Link hazır.', $link->result->successful);
    }

    checkoutResult($shown);

    if ($shown->transactions !== []) {
        echo '<h2>Ödemeler (son '.count($shown->transactions).' denemeden '.count($shown->successful()).' başarılı; toplam '.($shown->transactionsCount ?? count($shown->transactions)).' deneme)</h2><table>';

        foreach ($shown->transactions as $transaction) {
            echo '<tr><td>'.e($transaction->createdAt ?? '-').'</td><td>'.e(($transaction->status->value ?? '-').' '.$transaction->amount.' '.($transaction->currency->value ?? '').' '.($transaction->customer?->reference ?? '')).'</td></tr>';
        }

        echo '</table>';
    }

    echo '<form method="post"><input type="hidden" name="token" value="'.e($link->paymentLink->token).'">';
    echo '<div class="actions">';
    echo '<button class="button" type="submit" name="action" value="retrieve">Ödemeleriyle getir</button>';
    echo '<button class="button" type="submit" name="action" value="deactivate">Kapat</button>';
    echo '<a class="button" href="link.php">Yeni link</a><a class="button" href="index.php">Başa dön</a></div></form>';
} else {
    $_POST['reference'] ??= 'LNK-'.random_int(1000, 9999);

    form([
        'Link' => [
            'reference' => 'Link referansı (boş: geçit üretir)',
            'currency' => 'Para birimi',
            'description' => 'Açıklama',
            'expires_at' => 'Son gün (YYYY-AA-GG, boş: süresiz)',
        ],
        'Kalem' => itemFields(),
    ], $errors, 'Linki aç', ['reference', 'description', 'expires_at'], 'payment_link', ['action' => 'create']);

    echo '<form method="post"><input type="hidden" name="action" value="retrieve">';
    echo '<h2>Var olan linki getir</h2><div class="grid"><div'.(isset($errors['token']) ? ' class="invalid"' : '').'>';
    echo '<label for="token">Link token</label><input id="token" name="token" value="'.e(posted('token')).'">';
    echo '</div></div><div class="actions"><button class="button" type="submit">Getir</button></div></form>';
}

pageEnd();
