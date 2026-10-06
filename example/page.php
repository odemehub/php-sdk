<?php

declare(strict_types=1);

require_once __DIR__.'/config.php';

use Gurmehub\Odemehub\Exception\OdemehubException;
use Gurmehub\Odemehub\Exception\ValidationException;
use Gurmehub\Odemehub\Request\Address;
use Gurmehub\Odemehub\Request\Card;
use Gurmehub\Odemehub\Request\Customer;
use Gurmehub\Odemehub\Request\Item;
use Gurmehub\Odemehub\Response\GiveBack;
use Gurmehub\Odemehub\Response\Order;
use Gurmehub\Odemehub\Response\Payment;
use Gurmehub\Odemehub\Response\PaymentLink;
use Gurmehub\Odemehub\Response\Subscription;
use Gurmehub\Odemehub\Response\Transaction;

/*
|--------------------------------------------------------------------------
| Örneklerin ortak parçaları
|--------------------------------------------------------------------------
|
| Her sayfa aynı iskeleti, aynı form yardımcılarını ve aynı sonuç tablolarını
| kullanır. Sayfadan sayfaya değişen tek şey geçide atılan istek olduğu için
| geri kalan her şey burada durur.
|
*/

/**
 * Formların açıldığı örnek değerler. Referans her açılışta değişir ki aynı
 * referans iki kez gönderilmek zorunda kalınmasın.
 *
 * @return array<string, string>
 */
function dummy(): array
{
    return [
        'reference' => 'SIP-'.random_int(1000, 9999),
        'amount' => '100.50',
        'base_amount' => '',
        'currency' => 'TRY',
        'installment_number' => '1',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        'payment_provider_token' => '',

        'customer_reference' => 'musteri-1',
        'customer_firstname' => 'Ahmet',
        'customer_lastname' => 'Yılmaz',
        'customer_email' => 'ahmet@ornek.com',
        'customer_phone' => '05550000000',
        'customer_address' => 'Kızılırmak Mah. Dumlupınar Blv. No:3',
        'customer_district' => 'Çankaya',
        'customer_province' => 'Ankara',
        'customer_country' => 'TR',

        'card_holder_name' => 'Ahmet Yılmaz',
        'card_number' => '5528790000000008',
        'card_security_code' => '123',
        'card_expiry_month' => '12',
        'card_expiry_year' => '2030',
        'card_should_save' => '',

        'callback_url' => callbackUrl(),
        'success_url' => callbackUrl(),
        'item_name' => 'Deneme ürünü',
        'item_unit_amount' => '120.00',
        'item_quantity' => '1',
        'item_tax_rate' => '20',
    ];
}

/**
 * Bir alanın gösterilecek değeri: form geri geldiyse gönderilen değer, ilk
 * açılışta örnek değer.
 */
function value(string $field): string
{
    return isset($_POST[$field]) ? posted($field) : (dummy()[$field] ?? '');
}

/**
 * Gönderilen bir alanın değeri.
 */
function posted(string $field): string
{
    return trim((string) ($_POST[$field] ?? ''));
}

/**
 * Gönderilen bir alanın değeri, boşsa hiç.
 */
function postedOrNull(string $field): ?string
{
    return posted($field) === '' ? null : posted($field);
}

function isSubmitted(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/**
 * Formdaki müşteri: referansı ve fatura adresi.
 */
function postedCustomer(): Customer
{
    return new Customer(
        reference: postedOrNull('customer_reference'),
        billingAddress: new Address(
            firstname: postedOrNull('customer_firstname'),
            lastname: postedOrNull('customer_lastname'),
            email: postedOrNull('customer_email'),
            phone: postedOrNull('customer_phone'),
            address: postedOrNull('customer_address'),
            district: postedOrNull('customer_district'),
            province: postedOrNull('customer_province'),
            country: postedOrNull('customer_country'),
        ),
    );
}

/**
 * Formdaki kart.
 */
function postedCard(): Card
{
    return new Card(
        holderName: posted('card_holder_name'),
        number: posted('card_number'),
        expiryMonth: posted('card_expiry_month'),
        expiryYear: posted('card_expiry_year'),
        securityCode: posted('card_security_code'),
        shouldSave: posted('card_should_save') === '' ? null : true,
    );
}

/**
 * Formdaki tek kalem.
 */
function postedItem(): Item
{
    return new Item(
        name: posted('item_name'),
        unitAmount: posted('item_unit_amount'),
        quantity: (int) posted('item_quantity'),
        taxRate: postedOrNull('item_tax_rate'),
    );
}

/**
 * Formdaki ödeme, SecurePayment ya da RegularPayment'a adlandırılmış
 * argüman olarak açılacak biçimde: `new RegularPayment(...postedPayment())`.
 *
 * @return array<string, mixed>
 */
function postedPayment(): array
{
    return [
        'reference' => posted('reference'),
        'amount' => posted('amount'),
        'installmentNumber' => (int) posted('installment_number'),
        'ip' => posted('ip'),
        'customer' => postedCustomer(),
        'card' => postedOrNull('saved_card_token') === null ? postedCard() : null,
        'savedCardToken' => postedOrNull('saved_card_token'),
        'currency' => postedOrNull('currency') === null ? null : Currency::from(posted('currency')),
        'paymentProviderToken' => postedOrNull('payment_provider_token'),
        'baseAmount' => postedOrNull('base_amount'),
    ];
}

/**
 * Bir isteği atar; geçidin reddi formun hatalarına, kalan her şey mesaja
 * yazılır. Başarılı yanıt olduğu gibi döner.
 *
 * @template T
 *
 * @param  callable(): T  $call
 * @param  array<string, list<string>>  $errors
 * @return T|null
 */
function attempt(callable $call, ?string &$message, array &$errors): mixed
{
    try {
        return $call();
    } catch (ValidationException $exception) {
        $message = $exception->getMessage();
        $errors = $exception->errors;
    } catch (OdemehubException $exception) {
        $message = $exception->getMessage();
    }

    return null;
}

function e(?string $text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

function pageStart(string $title): void
{
    header('Content-Type: text/html; charset=utf-8');

    echo '<!doctype html><html lang="tr"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>'.e($title).' — ödemehub örneği</title>';
    echo '<style>
        :root { color-scheme: light dark; }
        body { margin: 0; padding: 2rem 1rem; font: 15px/1.5 system-ui, sans-serif; }
        main { max-width: 46rem; margin: 0 auto; }
        h1 { font-size: 1.4rem; margin: 0 0 1.5rem; }
        h2 { font-size: .95rem; margin: 1.75rem 0 .75rem; opacity: .6; }
        a { color: inherit; }
        p.lead { opacity: .7; margin: -1rem 0 1.5rem; }
        .actions { display: flex; gap: .75rem; flex-wrap: wrap; margin-top: 1.75rem; }
        .button { display: inline-block; padding: .7rem 1.2rem; border: 1px solid currentColor; border-radius: .5rem; text-decoration: none; font: inherit; font-weight: 600; cursor: pointer; background: none; color: inherit; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr)); gap: 1rem; }
        label { display: block; font-size: .8rem; opacity: .7; margin-bottom: .25rem; }
        .required { color: #dc2626; }
        input { width: 100%; box-sizing: border-box; padding: .5rem; border: 1px solid #8884; border-radius: .4rem; font: inherit; background: none; color: inherit; }
        .invalid input { border-color: #dc2626; }
        .error { color: #dc2626; font-size: .8rem; margin: .25rem 0 0; }
        .notice { padding: .75rem 1rem; border: 1px solid #dc2626; border-radius: .5rem; margin-bottom: 1.5rem; }
        .notice.ok { border-color: #16a34a; }
        table { border-collapse: collapse; width: 100%; }
        td { padding: .4rem .5rem; border-bottom: 1px solid #8884; vertical-align: top; font-family: ui-monospace, monospace; font-size: .85rem; }
        td:first-child { opacity: .6; white-space: nowrap; }
    </style></head><body><main>';
    echo '<h1>'.e($title).'</h1>';
}

function pageEnd(): void
{
    echo '</main></body></html>';
}

function notice(?string $message, bool $successful = false): void
{
    if ($message === null || $message === '') {
        return;
    }

    echo '<p class="notice'.($successful ? ' ok' : '').'">'.e($message).'</p>';
}

/**
 * Bir form alanının istek gövdesindeki karşılığı; alan hataları geçitten o
 * adla gelir: `customer_firstname` -> `customer.billing_address.firstname`.
 */
function parameter(string $field): string
{
    return match (true) {
        $field === 'customer_reference' => 'customer.reference',
        str_starts_with($field, 'customer_') => 'customer.billing_address.'.substr($field, 9),
        str_starts_with($field, 'card_') => 'card.'.substr($field, 5),
        str_starts_with($field, 'item_') => 'items.0.'.substr($field, 5),
        default => $field,
    };
}

/**
 * Başlıklar altında düz alanlar, her biri etiketi ve açılış değeriyle.
 * Geçitten dönen alan hataları hem alanın adıyla hem de gövdedeki noktalı
 * adıyla aranır; grup adı verildiyse (`order`, `transaction`) onun altında
 * da bakılır.
 *
 * @param  array<string, array<string, string>>  $sections
 * @param  array<string, list<string>>  $errors
 * @param  list<string>  $optional
 * @param  array<string, string>  $hidden
 */
function form(array $sections, array $errors, string $submitLabel, array $optional = [], ?string $group = null, array $hidden = []): void
{
    echo '<form method="post">';

    foreach ($hidden as $name => $value) {
        echo '<input type="hidden" name="'.e($name).'" value="'.e($value).'">';
    }

    foreach ($sections as $section => $labels) {
        echo '<h2>'.e($section).'</h2><div class="grid">';

        foreach ($labels as $field => $label) {
            $parameter = parameter($field);
            $error = $errors[$field][0] ?? $errors[$parameter][0] ?? ($group === null ? null : ($errors[$group.'.'.$parameter][0] ?? null));
            $required = in_array($field, $optional, true) ? '' : ' required';

            echo '<div'.($error === null ? '' : ' class="invalid"').'>';
            echo '<label for="'.e($field).'">'.e($label).($required === '' ? '' : ' <span class="required">*</span>').'</label>';
            echo '<input id="'.e($field).'" name="'.e($field).'" value="'.e(value($field)).'"'.$required.'>';

            if ($error !== null) {
                echo '<p class="error">'.e($error).'</p>';
            }

            echo '</div>';
        }

        echo '</div>';
    }

    echo '<div class="actions">';
    echo '<button class="button" type="submit">'.e($submitLabel).'</button>';
    echo '<a class="button" href="index.php">Vazgeç</a>';
    echo '</div></form>';
}

/**
 * Müşteri bölümünün alanları.
 *
 * @return array<string, string>
 */
function customerFields(): array
{
    return [
        'customer_reference' => 'Müşteri referansı (sizdeki)',
        'customer_firstname' => 'Ad',
        'customer_lastname' => 'Soyad',
        'customer_email' => 'E-posta',
        'customer_phone' => 'Telefon',
        'customer_address' => 'Adres',
        'customer_district' => 'İlçe',
        'customer_province' => 'İl',
        'customer_country' => 'Ülke',
    ];
}

/**
 * Kart bölümünün alanları.
 *
 * @return array<string, string>
 */
function cardFields(): array
{
    return [
        'card_holder_name' => 'Kart üzerindeki isim',
        'card_number' => 'Kart numarası',
        'card_security_code' => 'CVV',
        'card_expiry_month' => 'Son kullanma ayı',
        'card_expiry_year' => 'Son kullanma yılı',
        'card_should_save' => 'Kartı sakla (dolu: evet)',
    ];
}

/**
 * Tek kalemlik bölümün alanları.
 *
 * @return array<string, string>
 */
function itemFields(): array
{
    return [
        'item_name' => 'Kalem adı',
        'item_unit_amount' => 'Birim tutar (KDV dahil)',
        'item_quantity' => 'Adet',
        'item_tax_rate' => 'KDV oranı (%)',
    ];
}

/**
 * Ödeme formu: işlem, müşteri ve kart alanları, üstte test hesabı düğmeleri.
 *
 * @param  array<string, list<string>>  $errors
 * @param  array<string, array<string, string>>  $extraSections
 */
function paymentForm(array $errors = [], array $extraSections = []): void
{
    accountButtons();

    form([
        'İşlem' => [
            'reference' => 'Referans (sizdeki)',
            'amount' => 'Tutar',
            'base_amount' => 'Satılan tutar (boş: tutarla aynı)',
            'currency' => 'Para birimi',
            'installment_number' => 'Taksit',
            'ip' => 'Müşteri IP',
            'payment_provider_token' => 'Ödeme hesabı (boş: varsayılan)',
            'saved_card_token' => 'Kayıtlı kart token (dolu: kart alanları gönderilmez)',
        ],
        ...$extraSections,
        'Müşteri' => customerFields(),
        'Kart' => cardFields(),
    ], $errors, 'Ödeme yap', ['base_amount', 'currency', 'payment_provider_token', 'saved_card_token', 'card_should_save', 'customer_reference'], 'transaction');
}

/**
 * Hangi ödeme hesabıyla deneneceğini seçen düğmeler. Seçilen hesabın token'ı
 * ve o sağlayıcının kendi test kartı forma yazılır.
 */
function accountButtons(): void
{
    echo '<h2>Test hesabı</h2><div class="actions">';

    foreach (accounts() as $label => $account) {
        $password = $account['secure_password'];
        unset($account['secure_password']);

        echo '<button type="button" class="button" data-account="'.e(json_encode($account)).'"';
        echo ($password === '' ? '' : ' title="3D şifresi: '.e($password).'"').'>'.e($label).'</button>';
    }

    echo '</div>';
    echo '<script>
        document.querySelectorAll("[data-account]").forEach((button) => {
            button.addEventListener("click", () => {
                Object.entries(JSON.parse(button.dataset.account)).forEach(([name, value]) => {
                    const field = document.querySelector(`[name="${name}"]`);
                    if (field) field.value = value;
                });
            });
        });
    </script>';
}

/**
 * Bir ödeme sonucunun tablosu.
 *
 * @param  array<string, string|null>  $extra
 */
function paymentResult(Payment $payment, array $extra = []): void
{
    notice($payment->result->message ?? ($payment->result->successful ? 'Ödeme başarılı.' : 'Ödeme bekliyor.'), $payment->result->successful);

    echo '<table>';
    echo '<tr><td>result.successful</td><td>'.var_export($payment->result->successful, true).'</td></tr>';
    echo '<tr><td>transaction.token</td><td>'.e($payment->transaction->token).'</td></tr>';
    echo '<tr><td>transaction.reference</td><td>'.e($payment->transaction->reference).'</td></tr>';
    echo '<tr><td>transaction.status / payment_status</td><td>'.e(($payment->transaction->status->value ?? '-').' / '.($payment->transaction->paymentStatus->value ?? '-')).'</td></tr>';
    echo '<tr><td>transaction.amount</td><td>'.e(($payment->transaction->amount ?? '-').' '.($payment->transaction->currency->value ?? '').' ('.($payment->transaction->installmentNumber ?? 1).' taksit)').'</td></tr>';
    echo '<tr><td>transaction.is_test</td><td>'.var_export($payment->transaction->isTest, true).'</td></tr>';
    echo '<tr><td>customer.reference</td><td>'.e($payment->customer?->reference ?? '-').'</td></tr>';
    echo '<tr><td>conversion</td><td>'.e($payment->conversion === null ? '-' : $payment->conversion->amount.' '.($payment->conversion->currency->value ?? '').' @ '.$payment->conversion->rate).'</td></tr>';

    if ($payment->savedCard !== null) {
        echo '<tr><td>saved_card.token</td><td>'.e($payment->savedCard->token).'</td></tr>';
        echo '<tr><td>saved_card</td><td>'.e($payment->savedCard->firstDigits.'****'.$payment->savedCard->lastFourDigit).'</td></tr>';
    }

    if ($payment instanceof GiveBack && $payment->refund !== null) {
        echo '<tr><td>refund.type</td><td>'.e($payment->refund->type->value ?? '-').'</td></tr>';
        echo '<tr><td>refund.amount</td><td>'.e($payment->refund->amount).'</td></tr>';
    }

    foreach ($extra as $field => $value) {
        echo '<tr><td>'.e($field).'</td><td>'.e($value ?? '-').'</td></tr>';
    }

    echo '</table>';
    echo '<div class="actions"><a class="button" href="index.php">Başa dön</a></div>';
}

/**
 * Sorgulanan bir ödemenin tablosu: retrieve-payments listesinin bir öğesi.
 */
function transactionResult(Transaction $transaction): void
{
    notice($transaction->errorMessage ?? ($transaction->isSuccessful() ? 'Ödeme başarılı.' : 'Ödeme: '.($transaction->status->value ?? '-')), $transaction->isSuccessful());

    echo '<table>';
    echo '<tr><td>token</td><td>'.e($transaction->token).'</td></tr>';
    echo '<tr><td>reference</td><td>'.e($transaction->reference).'</td></tr>';
    echo '<tr><td>status / payment_status</td><td>'.e(($transaction->status->value ?? '-').' / '.($transaction->paymentStatus->value ?? '-')).'</td></tr>';
    echo '<tr><td>amount</td><td>'.e($transaction->amount.' '.($transaction->currency->value ?? '').' ('.$transaction->installmentNumber.' taksit)').'</td></tr>';
    echo '<tr><td>security_type</td><td>'.e($transaction->securityType->value ?? '-').'</td></tr>';
    echo '<tr><td>is_test</td><td>'.var_export($transaction->isTest, true).'</td></tr>';
    echo '<tr><td>customer.reference</td><td>'.e($transaction->customer?->reference ?? '-').'</td></tr>';
    echo '<tr><td>error_message</td><td>'.e($transaction->errorMessage ?? '-').'</td></tr>';
    echo '<tr><td>created_at</td><td>'.e($transaction->createdAt ?? '-').'</td></tr>';
    echo '<tr><td>order / link / subscription</td><td>'.e(($transaction->orderToken ?? '-').' / '.($transaction->paymentLinkToken ?? '-').' / '.($transaction->subscriptionToken ?? '-')).'</td></tr>';

    if ($transaction->savedCard !== null) {
        echo '<tr><td>saved_card</td><td>'.e($transaction->savedCard->token.' ('.$transaction->savedCard->firstDigits.'****'.$transaction->savedCard->lastFourDigit.')').'</td></tr>';
    }

    echo '</table><br>';
}

/**
 * Bir siparişin, aboneliğin ya da ödeme linkinin tablosu.
 */
function checkoutResult(Order|Subscription|PaymentLink $thing): void
{
    echo '<table>';
    echo '<tr><td>token</td><td>'.e($thing->token).'</td></tr>';
    echo '<tr><td>reference</td><td>'.e($thing->reference).'</td></tr>';

    if (! $thing instanceof PaymentLink) {
        echo '<tr><td>status</td><td>'.e($thing->status->value ?? '-').'</td></tr>';
        echo '<tr><td>shipping_amount</td><td>'.e($thing->shippingAmount).'</td></tr>';
    }

    if ($thing instanceof Subscription) {
        echo '<tr><td>period</td><td>'.e($thing->period->value ?? '-').'</td></tr>';
        echo '<tr><td>renewal_limit</td><td>'.e((string) ($thing->renewalLimit ?? '-')).'</td></tr>';
        echo '<tr><td>renewals_paid</td><td>'.e((string) $thing->renewalsPaid).'</td></tr>';
        echo '<tr><td>renewal.token</td><td>'.e($thing->renewal->token).'</td></tr>';
        echo '<tr><td>next_payment_at</td><td>'.e($thing->nextPaymentAt ?? '-').'</td></tr>';
        echo '<tr><td>cancelled_at</td><td>'.e($thing->cancelledAt ?? '-').'</td></tr>';
    }

    if ($thing instanceof PaymentLink) {
        echo '<tr><td>is_active</td><td>'.var_export($thing->isActive, true).'</td></tr>';
        echo '<tr><td>expires_at</td><td>'.e($thing->expiresAt ?? '-').'</td></tr>';
        echo '<tr><td>is_test</td><td>'.var_export($thing->isTest, true).'</td></tr>';
        echo '<tr><td>amount_type</td><td>'.e($thing->amountType->value ?? '-').'</td></tr>';
    } else {
        echo '<tr><td>customer.reference</td><td>'.e($thing->customer?->reference ?? '-').'</td></tr>';
        echo '<tr><td>discount</td><td>'.e($thing->discount === null ? '-' : $thing->discount->code.' -'.$thing->discount->amount).'</td></tr>';
    }

    echo '<tr><td>subtotal</td><td>'.e($thing->subtotal ?? '-').'</td></tr>';
    echo '<tr><td>tax_amount</td><td>'.e($thing->taxAmount ?? '-').'</td></tr>';
    echo '<tr><td>amount</td><td>'.e($thing->amount === null ? '-' : $thing->amount.' '.($thing->currency->value ?? '')).'</td></tr>';
    echo '<tr><td>checkout_url</td><td>'.($thing->checkoutUrl === null ? '-' : '<a href="'.e($thing->checkoutUrl).'">'.e($thing->checkoutUrl).'</a>').'</td></tr>';

    if ($thing instanceof Order && $thing->transaction !== null) {
        echo '<tr><td>transaction.token</td><td>'.e($thing->transaction->token).'</td></tr>';
    }

    echo '</table>';
}
