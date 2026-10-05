<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Enum\Period;
use Gurmehub\Odemehub\Enum\SubscriptionStatus;
use Gurmehub\Odemehub\Request\CreateSubscription;
use Gurmehub\Odemehub\Request\RetrieveSubscriptions;
use Gurmehub\Odemehub\Request\UpdateSubscription;

/*
|--------------------------------------------------------------------------
| Abonelik
|--------------------------------------------------------------------------
|
| Abonelik açılır; ilk yenileme geçidin ödeme sayfasında ödenir ve kart orada
| saklanır. Sonraki yenilemeler o karttan çekilir ve her biri panelde
| kanala tanımlanan webhook adresine bildirilir. Müşteri referansı zorunludur: kart onun altında
| saklanır. İptal, update-subscription ile `status: cancelled` göndermektir;
| ödenmiş dönem sonuna kadar sürer, sonrasında çekim yapılmaz.
|
*/

$message = null;

/** @var array<string, list<string>> $errors */
$errors = [];

$subscription = match (isSubmitted() ? posted('action') : null) {
    'create' => attempt(fn () => client()->createSubscription(new CreateSubscription(
        reference: posted('reference'),
        period: Period::from(posted('period')),
        successUrl: posted('success_url'),
        items: [postedItem()],
        customer: postedCustomer(),
        renewalLimit: postedOrNull('renewal_limit') === null ? null : (int) posted('renewal_limit'),
        description: postedOrNull('description'),
    )), $message, $errors),
    'cancel' => attempt(fn () => client()->updateSubscription(new UpdateSubscription(
        token: posted('token'),
        status: SubscriptionStatus::Cancelled,
    )), $message, $errors),
    default => null,
};

$found = isSubmitted() && posted('action') === 'retrieve'
    ? attempt(fn () => client()->retrieveSubscriptions(RetrieveSubscriptions::byToken(posted('token'))), $message, $errors)?->subscriptions[0] ?? null
    : null;

if (isSubmitted() && posted('action') === 'retrieve' && $found === null) {
    $message ??= 'Bu token ile abonelik bulunamadı.';
}

pageStart('Abonelik');

notice($message);

if ($subscription !== null) {
    notice($subscription->result->message ?? 'Abonelik hazır.', $subscription->result->successful);
    checkoutResult($subscription->subscription);
    echo '<form method="post"><input type="hidden" name="action" value="cancel"><input type="hidden" name="token" value="'.e($subscription->subscription->token).'">';
    echo '<div class="actions"><button class="button" type="submit">İptal et</button><a class="button" href="subscription.php">Yeni abonelik</a><a class="button" href="index.php">Başa dön</a></div></form>';
} elseif ($found !== null) {
    checkoutResult($found);
    echo '<form method="post"><input type="hidden" name="action" value="cancel"><input type="hidden" name="token" value="'.e($found->token).'">';
    echo '<div class="actions"><button class="button" type="submit">İptal et</button><a class="button" href="subscription.php">Yeni abonelik</a><a class="button" href="index.php">Başa dön</a></div></form>';
} else {
    $_POST['period'] ??= 'monthly';

    form([
        'Abonelik' => [
            'reference' => 'Abonelik referansı (sizdeki)',
            'period' => 'Dönem (daily, weekly, monthly, annually)',
            'renewal_limit' => 'Ödeme sayısı (boş: iptale kadar)',
            'description' => 'Açıklama',
            'success_url' => 'Başarı adresi',
        ],
        'Kalem' => itemFields(),
        'Müşteri' => customerFields(),
    ], $errors, 'Ödeme sayfasını aç', ['renewal_limit', 'description', ...array_diff(array_keys(customerFields()), ['customer_reference'])], 'subscription', ['action' => 'create']);

    echo '<form method="post"><input type="hidden" name="action" value="retrieve">';
    echo '<h2>Var olan aboneliği getir</h2><div class="grid"><div'.(isset($errors['token']) ? ' class="invalid"' : '').'>';
    echo '<label for="token">Abonelik token</label><input id="token" name="token" value="'.e(posted('token')).'">';
    echo '</div></div><div class="actions"><button class="button" type="submit">Getir</button></div></form>';
}

pageEnd();
