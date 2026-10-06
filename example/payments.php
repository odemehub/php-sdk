<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Request\RetrievePayments;

/*
|--------------------------------------------------------------------------
| Ödemeler
|--------------------------------------------------------------------------
|
| İki soru: bir referansın son ödemesi nasıl gitti, ve çalışma alanında bir
| tarih aralığında hangi denemeler yapıldı. İlki yanıtı alınamayan bir ödemenin
| akıbetini öğrenmek içindir; ikincisi her denemenin durumunu, tutarını ve
| paranın ne olduğunu listeler. Hiçbir şey değişmez.
|
*/

$message = null;

/** @var array<string, list<string>> $errors */
$errors = [];

$list = match (isSubmitted() ? posted('action') : null) {
    'reference' => attempt(fn () => client()->retrievePayments(RetrievePayments::byReference(posted('reference'))), $message, $errors),
    'list' => attempt(fn () => client()->retrievePayments(new RetrievePayments(
        createdFrom: postedOrNull('created_from'),
        createdTo: postedOrNull('created_to'),
    )), $message, $errors),
    default => null,
};

pageStart('Ödemeler');

notice($message);

echo '<form method="post"><input type="hidden" name="action" value="reference">';
echo '<h2>Referansla</h2><div class="grid"><div'.(isset($errors['reference']) ? ' class="invalid"' : '').'>';
echo '<label for="reference">Referans (sizdeki)</label>';
echo '<input id="reference" name="reference" value="'.e(posted('reference')).'" required>';
echo '</div></div>';
echo '<div class="actions"><button class="button" type="submit">Denemeleri getir</button></div></form>';

echo '<form method="post"><input type="hidden" name="action" value="list">';
echo '<h2>Tarih aralığıyla (en çok 7 gün; boş: son 7 gün)</h2><div class="grid">';
echo '<div><label for="created_from">Başlangıç (YYYY-AA-GG)</label><input id="created_from" name="created_from" value="'.e(posted('created_from')).'"></div>';
echo '<div><label for="created_to">Bitiş (YYYY-AA-GG)</label><input id="created_to" name="created_to" value="'.e(posted('created_to')).'"></div>';
echo '</div>';
echo '<div class="actions"><button class="button" type="submit">Listele</button><a class="button" href="index.php">Başa dön</a></div></form>';

if ($list !== null) {
    echo '<h2>'.e($list->createdFrom === null ? 'Referansla' : $list->createdFrom.' – '.$list->createdTo).' ('.count($list->payments).' deneme)</h2>';

    foreach ($list->payments as $transaction) {
        transactionResult($transaction);
    }
}

pageEnd();
