<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Request\RetrieveBin;

/*
|--------------------------------------------------------------------------
| Kart sorgulama
|--------------------------------------------------------------------------
|
| Kartın ilk altı ile sekiz hanesiyle bankası, tipi ve verilen tutar için
| taksit seçenekleri sorulur. Hiçbir şey çekilmez, hiçbir şey yazılmaz.
| Hesap verilmezse ödeme hangi hesaptan geçecekse o sorulur.
|
*/

$message = null;

/** @var array<string, list<string>> $errors */
$errors = [];

$bin = isSubmitted()
    ? attempt(fn () => client()->retrieveBin(new RetrieveBin(
        bin: posted('bin'),
        amount: posted('amount'),
        paymentProviderToken: postedOrNull('payment_provider_token'),
    )), $message, $errors)
    : null;

pageStart('Kart sorgulama');

notice($bin?->result->message ?? $message, $bin?->result->successful ?? false);

$_POST['bin'] ??= '454671';
$_POST['amount'] ??= dummy()['amount'];

form([
    'Kart' => [
        'bin' => 'Kartın ilk haneleri',
        'amount' => 'Tutar',
        'payment_provider_token' => 'Ödeme hesabı (boş: varsayılan)',
    ],
], array_merge($errors, ['bin' => $errors['card.bin'] ?? []]), 'Sorgula', ['payment_provider_token'], 'transaction');

if ($bin !== null) {
    echo '<h2>Kart</h2><table>';
    echo '<tr><td>banka</td><td>'.e($bin->issuerName ?? '-').' ('.e($bin->issuerCode ?? '-').')</td></tr>';
    echo '<tr><td>şema / tip</td><td>'.e(($bin->scheme->value ?? '-').' / '.($bin->type->value ?? '-')).'</td></tr>';
    echo '<tr><td>program</td><td>'.e($bin->program ?? '-').'</td></tr>';
    echo '<tr><td>ticari kart</td><td>'.var_export($bin->isCommercial, true).'</td></tr>';
    echo '</table>';

    echo '<h2>Taksitler ('.count($bin->installments).')</h2><table>';

    foreach ($bin->installments as $installment) {
        echo '<tr><td>'.e((string) $installment->number).' taksit</td><td>'.e($installment->amount).' x '.e((string) $installment->number).' = '.e($installment->total).'</td></tr>';
    }

    echo '</table>';
}

pageEnd();
