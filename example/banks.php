<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Request\CreateBankAccount;
use Gurmehub\Odemehub\Request\DeleteBankAccount;
use Gurmehub\Odemehub\Request\RetrieveBankAccounts;
use Gurmehub\Odemehub\Request\RetrieveBanks;
use Gurmehub\Odemehub\Request\RetrieveBankTransactions;
use Gurmehub\Odemehub\Request\UpdateBankAccount;

/*
|--------------------------------------------------------------------------
| Banka hareketleri
|--------------------------------------------------------------------------
|
| Hareketleri okunacak banka hesabı, bankanın istediği kimlik bilgileriyle
| eklenir; geçit önce bankaya sorar, bankanın reddettiği hesap eklenmez ve
| neden result.message'da gelir. Hesap bundan sonra yalnızca token'ıyla
| anılır: adı ve kimlik bilgileri değiştirilir, silinir, hareketleri en çok
| 7 günlük aralıkla, sayfa sayfa sorulur. Hareketler bankadan değil geçidin
| sakladığı kayıttan okunur; geçit her hesabı saatte bir bankadan çeker.
|
*/

$message = null;

/** @var array<string, list<string>> $errors */
$errors = [];

/** @var array<string, string> $settings */
$settings = [];

foreach (array_keys($_POST) as $field) {
    if (is_string($field) && str_starts_with($field, 'settings_')) {
        $settings[substr($field, 9)] = posted($field);
    }
}

$outcome = match (isSubmitted() ? posted('action') : null) {
    'create' => attempt(fn () => client()->createBankAccount(new CreateBankAccount(
        provider: posted('provider'),
        name: posted('name'),
        iban: posted('iban'),
        settings: $settings,
    )), $message, $errors),
    'rename' => attempt(fn () => client()->updateBankAccount(new UpdateBankAccount(
        token: posted('bank_account_token'),
        name: posted('new_name'),
    )), $message, $errors),
    'delete' => attempt(fn () => client()->deleteBankAccount(new DeleteBankAccount(posted('bank_account_token'))), $message, $errors),
    default => null,
};

$movements = isSubmitted() && posted('action') === 'transactions'
    ? attempt(fn () => client()->retrieveBankTransactions(new RetrieveBankTransactions(
        bankAccountTokens: [posted('bank_account_token')],
        createdFrom: postedOrNull('created_from'),
        createdTo: postedOrNull('created_to'),
        page: postedOrNull('page') === null ? null : (int) posted('page'),
    )), $message, $errors)
    : null;

$banks = attempt(fn () => client()->retrieveBanks(new RetrieveBanks), $message, $errors);
$accounts = attempt(fn () => client()->retrieveBankAccounts(new RetrieveBankAccounts), $message, $errors);

pageStart('Banka hareketleri');

if ($outcome !== null) {
    notice($outcome->result->message ?? 'Tamam.', $outcome->result->successful);
} else {
    notice($message);
}

foreach ($banks?->banks ?? [] as $bank) {
    $fields = [];
    $optional = [];

    foreach ($bank->fields as $field) {
        $fields['settings_'.$field->key] = $field->label;

        if (! $field->required) {
            $optional[] = 'settings_'.$field->key;
        }
    }

    form([
        $bank->title.' hesabı ekle' => [
            'name' => 'Hesap adı',
            'iban' => 'IBAN',
        ],
        'Kimlik bilgileri' => $fields,
    ], $errors, 'Hesabı ekle', $optional, 'bank_account', ['action' => 'create', 'provider' => $bank->id]);
}

if ($accounts !== null) {
    echo '<h2>Hesaplar ('.count($accounts->bankAccounts).')</h2>';

    if ($accounts->bankAccounts === []) {
        echo '<p class="lead">Çalışma alanında banka hesabı yok.</p>';
    }

    foreach ($accounts->bankAccounts as $account) {
        echo '<form method="post"><table>';
        echo '<tr><td>token</td><td>'.e($account->token).'</td></tr>';
        echo '<tr><td>banka</td><td>'.e($account->provider).'</td></tr>';
        echo '<tr><td>ad</td><td>'.e($account->name).'</td></tr>';
        echo '<tr><td>iban</td><td>'.e($account->iban).'</td></tr>';
        echo '<tr><td>synced_at</td><td>'.e($account->syncedAt ?? '-').'</td></tr>';
        echo '<tr><td>last_error</td><td>'.e($account->lastError ?? '-').'</td></tr>';
        echo '<tr><td>created_at</td><td>'.e($account->createdAt ?? '-').'</td></tr>';
        echo '</table>';

        echo '<input type="hidden" name="bank_account_token" value="'.e($account->token).'">';
        echo '<div class="grid"><div><label for="new_name_'.e($account->token).'">Yeni ad</label><input id="new_name_'.e($account->token).'" name="new_name" value="'.e($account->name).'"></div></div>';
        echo '<div class="actions">';
        echo '<button class="button" type="submit" name="action" value="transactions">Hareketler</button>';
        echo '<button class="button" type="submit" name="action" value="rename">Adı değiştir</button>';
        echo '<button class="button" type="submit" name="action" value="delete">Sil</button>';
        echo '</div></form>';
    }
}

echo '<form method="post"><input type="hidden" name="action" value="transactions">';
echo '<h2>Hareketler (en çok 7 gün; boş: son 7 gün)</h2><div class="grid">';
echo '<div'.(isset($errors['bank_account_tokens.0']) ? ' class="invalid"' : '').'><label for="bank_account_token">Banka hesabı token <span class="required">*</span></label><input id="bank_account_token" name="bank_account_token" value="'.e(posted('bank_account_token')).'" required></div>';
echo '<div'.(isset($errors['created_to']) ? ' class="invalid"' : '').'><label for="created_from">Başlangıç (YYYY-AA-GG)</label><input id="created_from" name="created_from" value="'.e(posted('created_from')).'"></div>';
echo '<div><label for="created_to">Bitiş (YYYY-AA-GG)</label><input id="created_to" name="created_to" value="'.e(posted('created_to')).'"></div>';
echo '<div><label for="page">Sayfa (boş: 1)</label><input id="page" name="page" value="'.e(posted('page')).'"></div>';
echo '</div>';
echo '<div class="actions"><button class="button" type="submit">Hareketleri getir</button><a class="button" href="index.php">Başa dön</a></div></form>';

if ($movements !== null) {
    echo '<h2>'.e($movements->createdFrom.' – '.$movements->createdTo).' (sayfa '.$movements->pagination->page.' / '.$movements->pagination->lastPage.'; toplam '.$movements->pagination->total.' hareket)</h2>';

    if ($movements->bankTransactions === []) {
        echo '<p class="lead">Bu aralıkta hareket yok.</p>';
    }

    echo '<table>';

    foreach ($movements->bankTransactions as $movement) {
        echo '<tr><td>'.e($movement->bookedAt).'</td><td>'.e($movement->amount.' '.($movement->currency->value ?? '')).' — '.e($movement->description ?? '-');
        echo $movement->counterpartyIban === null ? '' : '<br>'.e($movement->counterpartyIban);
        echo $movement->balanceAfter === null ? '' : '<br>bakiye: '.e($movement->balanceAfter);
        echo '</td></tr>';
    }

    echo '</table>';

    echo '<form method="post"><input type="hidden" name="action" value="transactions">';
    echo '<input type="hidden" name="bank_account_token" value="'.e(posted('bank_account_token')).'">';
    echo '<input type="hidden" name="created_from" value="'.e($movements->createdFrom).'">';
    echo '<input type="hidden" name="created_to" value="'.e($movements->createdTo).'">';
    echo '<div class="actions">';
    echo $movements->pagination->page > 1 ? '<button class="button" type="submit" name="page" value="'.($movements->pagination->page - 1).'">Önceki sayfa</button>' : '';
    echo $movements->pagination->hasMorePages() ? '<button class="button" type="submit" name="page" value="'.($movements->pagination->page + 1).'">Sonraki sayfa</button>' : '';
    echo '</div></form>';
}

pageEnd();
