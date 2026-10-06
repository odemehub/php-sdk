<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Request\CreateSavedCard;
use Gurmehub\Odemehub\Request\DeleteSavedCard;
use Gurmehub\Odemehub\Request\RetrieveSavedCards;
use Gurmehub\Odemehub\Request\UpdateSavedCard;

/*
|--------------------------------------------------------------------------
| Kayıtlı kartlar
|--------------------------------------------------------------------------
|
| Bir kart ödeme yapılmadan saklanır, müşterinin kartları listelenir, biri
| varsayılan yapılır ya da silinir. Kart müşteri referansının altında
| durur; liste de bu referansla sorulur. Liste kartı çekebilecek bir şey
| döndürmez; kart yalnızca ilk ve son haneleriyle anılır.
|
*/

$message = null;

/** @var array<string, list<string>> $errors */
$errors = [];

$outcome = match (isSubmitted() ? posted('action') : null) {
    'create' => attempt(fn () => client()->createSavedCard(new CreateSavedCard(
        customer: postedCustomer(),
        card: postedCard(),
        paymentProviderToken: postedOrNull('payment_provider_token'),
    )), $message, $errors),
    'default' => attempt(fn () => client()->updateSavedCard(new UpdateSavedCard(posted('saved_card_token'))), $message, $errors),
    'delete' => attempt(fn () => client()->deleteSavedCard(new DeleteSavedCard(posted('saved_card_token'))), $message, $errors),
    default => null,
};

$cards = isSubmitted() && posted('customer_reference') !== ''
    ? attempt(fn () => client()->retrieveSavedCards(RetrieveSavedCards::byReference(posted('customer_reference'))), $message, $errors)
    : null;

pageStart('Kayıtlı kartlar');

if ($outcome !== null) {
    notice($outcome->result->message ?? 'Tamam.', $outcome->result->successful);
} else {
    notice($message);
}

accountButtons();

form([
    'Müşteri' => customerFields(),
    'Kart' => array_diff_key(cardFields(), ['card_should_save' => '']),
    'Hesap' => ['payment_provider_token' => 'Ödeme hesabı (boş: varsayılan)'],
], $errors, 'Kartı sakla', ['payment_provider_token', 'card_security_code'], null, ['action' => 'create']);

if ($cards !== null) {
    echo '<h2>'.e(posted('customer_reference')).' — kartlar ('.count($cards->savedCards).')</h2>';

    if ($cards->savedCards === []) {
        echo '<p class="lead">Bu müşterinin kayıtlı kartı yok.</p>';
    }

    foreach ($cards->savedCards as $card) {
        echo '<form method="post"><table>';
        echo '<tr><td>token</td><td>'.e($card->token).'</td></tr>';
        echo '<tr><td>kart</td><td>'.e($card->firstDigits.'****'.$card->lastFourDigit).' ('.e($card->scheme->value ?? '-').')</td></tr>';
        echo '<tr><td>son kullanma</td><td>'.e($card->expiryMonth.'/'.$card->expiryYear).'</td></tr>';
        echo '<tr><td>ödeme hesabı</td><td>'.e($card->paymentProviderToken).'</td></tr>';
        echo '<tr><td>varsayılan</td><td>'.var_export($card->isDefault, true).'</td></tr>';
        echo '</table>';

        echo '<input type="hidden" name="customer_reference" value="'.e(posted('customer_reference')).'">';

        echo '<input type="hidden" name="saved_card_token" value="'.e($card->token).'">';
        echo '<div class="actions">';
        echo $card->isDefault ? '' : '<button class="button" type="submit" name="action" value="default">Varsayılan yap</button>';
        echo '<button class="button" type="submit" name="action" value="delete">Sil</button>';
        echo '</div></form>';
    }
}

pageEnd();
