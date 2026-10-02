<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Request\RetrievePaymentByReference;
use Gurmehub\Odemehub\Request\RetrievePaymentsByChannelReference;

/*
|--------------------------------------------------------------------------
| Ödemeler
|--------------------------------------------------------------------------
|
| İki soru: bir referansın son ödemesi nasıl gitti, ve kanalda bir tarih
| aralığında hangi denemeler yapıldı. İlki yanıtı alınamayan bir ödemenin
| akıbetini öğrenmek içindir; ikincisi her denemenin durumunu, tutarını ve
| paranın ne olduğunu listeler. Hiçbir şey değişmez.
|
*/

$message = null;

/** @var array<string, list<string>> $errors */
$errors = [];

$payment = null;
$list = null;

if (isSubmitted() && posted('action') === 'reference') {
    $payment = attempt(fn () => client()->retrievePaymentByReference(new RetrievePaymentByReference(posted('reference'))), $message, $errors);
}

if (isSubmitted() && posted('action') === 'list') {
    $list = attempt(fn () => client()->retrievePaymentsByChannelReference(new RetrievePaymentsByChannelReference(
        createdFrom: postedOrNull('created_from'),
        createdTo: postedOrNull('created_to'),
    )), $message, $errors);
}

pageStart('Ödemeler');

notice($message);

echo '<form method="post"><input type="hidden" name="action" value="reference">';
echo '<h2>Referansla</h2><div class="grid"><div'.(isset($errors['channel_reference']) ? ' class="invalid"' : '').'>';
echo '<label for="reference">Referans (sizdeki)</label>';
echo '<input id="reference" name="reference" value="'.e(posted('reference')).'" required>';
echo '</div></div>';
echo '<div class="actions"><button class="button" type="submit">Son ödemeyi getir</button></div></form>';

if ($payment !== null) {
    paymentResult($payment);
}

echo '<form method="post"><input type="hidden" name="action" value="list">';
echo '<h2>Tarih aralığıyla (en çok 7 gün; boş: son 7 gün)</h2><div class="grid">';
echo '<div><label for="created_from">Başlangıç (YYYY-AA-GG)</label><input id="created_from" name="created_from" value="'.e(posted('created_from')).'"></div>';
echo '<div><label for="created_to">Bitiş (YYYY-AA-GG)</label><input id="created_to" name="created_to" value="'.e(posted('created_to')).'"></div>';
echo '</div>';
echo '<div class="actions"><button class="button" type="submit">Listele</button><a class="button" href="index.php">Başa dön</a></div></form>';

if ($list !== null) {
    echo '<h2>'.e($list->createdFrom).' – '.e($list->createdTo).' ('.count($list->payments).' deneme)</h2>';

    foreach ($list->payments as $transaction) {
        echo '<table>';
        echo '<tr><td>token</td><td>'.e($transaction->token).'</td></tr>';
        echo '<tr><td>channel_reference</td><td>'.e($transaction->channelReference).'</td></tr>';
        echo '<tr><td>status / payment_status</td><td>'.e(($transaction->status->value ?? '-').' / '.($transaction->paymentStatus->value ?? '-')).'</td></tr>';
        echo '<tr><td>amount</td><td>'.e($transaction->amount.' '.($transaction->currency->value ?? '').' ('.$transaction->installmentNumber.' taksit)').'</td></tr>';
        echo '<tr><td>security_type</td><td>'.e($transaction->securityType->value ?? '-').'</td></tr>';
        echo '<tr><td>error_message</td><td>'.e($transaction->errorMessage ?? '-').'</td></tr>';
        echo '<tr><td>created_at</td><td>'.e($transaction->createdAt ?? '-').'</td></tr>';
        echo '<tr><td>order / link / subscription</td><td>'.e(($transaction->orderToken ?? '-').' / '.($transaction->paymentLinkToken ?? '-').' / '.($transaction->subscriptionToken ?? '-')).'</td></tr>';
        echo '</table><br>';
    }
}

pageEnd();
