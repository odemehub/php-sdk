<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Request\CancelPayment;
use Gurmehub\Odemehub\Request\RefundPayment;

/*
|--------------------------------------------------------------------------
| İade ve iptal
|--------------------------------------------------------------------------
|
| Ödemenin geçitteki token'ı yeterlidir; hesabı, sağlayıcıyı ve sağlayıcının
| ödemeye verdiği referansı geçit zaten bilir. İade tutarı boş bırakılırsa
| iade edilebilir kalanın tamamı geri verilir. İptalde tutar yoktur: iptal
| her zaman tamamıdır ve sağlayıcı ödemeyi henüz kapatmamışken yapılır.
|
*/

$message = null;

/** @var array<string, list<string>> $errors */
$errors = [];

$result = match (isSubmitted() ? posted('action') : null) {
    'refund' => attempt(fn () => client()->refundPayment(new RefundPayment(posted('transaction_token'), postedOrNull('amount'))), $message, $errors),
    'cancel' => attempt(fn () => client()->cancelPayment(new CancelPayment(posted('transaction_token'))), $message, $errors),
    default => null,
};

pageStart('İade / iptal');

if ($result !== null) {
    paymentResult($result);
} else {
    notice($message);

    echo '<form method="post"><h2>Ödeme</h2><div class="grid">';

    foreach (['transaction_token' => ["İşlem token'ı (transaction.token)", 'transaction.token'], 'amount' => ['İade tutarı (boş: kalanın tamamı)', 'amount']] as $field => [$label, $parameter]) {
        $error = $errors[$parameter][0] ?? null;
        echo '<div'.($error === null ? '' : ' class="invalid"').'><label for="'.e($field).'">'.e($label).'</label>';
        echo '<input id="'.e($field).'" name="'.e($field).'" value="'.e(posted($field)).'"'.($field === 'amount' ? '' : ' required').'>';
        echo $error === null ? '' : '<p class="error">'.e($error).'</p>';
        echo '</div>';
    }

    echo '</div><div class="actions">';
    echo '<button class="button" type="submit" name="action" value="refund">İade et</button>';
    echo '<button class="button" type="submit" name="action" value="cancel">İptal et</button>';
    echo '<a class="button" href="index.php">Vazgeç</a></div></form>';
}

pageEnd();
