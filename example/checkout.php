<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

use Gurmehub\Odemehub\Exception\OdemehubException;
use Gurmehub\Odemehub\Exception\ValidationException;
use Gurmehub\Odemehub\Request\OrderItem;
use Gurmehub\Odemehub\Request\OrderPayment;

/*
|--------------------------------------------------------------------------
| Ödeme sayfası (checkout)
|--------------------------------------------------------------------------
|
| Burada kart hiç sorulmaz: sipariş açılır ve geçit, müşterinin kartını
| gireceği kendi sayfasının adresini döndürür. Müşteri oraya yönlendirilir,
| ödemeyi orada yapar ve sonuç `success_url` adresine imzalı olarak POST
| edilir — 3D dönüşüyle birebir aynı biçimde, yani return.php onu da okur.
|
| Sipariş tutarı gönderilmez; geçit kalemleri toplar. Her kalem bir ürünü
| sizdeki referansıyla adlandırır ve boş bıraktığı ad, fiyat ve KDV kayıtlı
| üründen doldurulur. Kayıtlı olmayan bir referans için ad ve birim fiyat
| gönderilmelidir. Müşteri bütün olarak gönderilir, çünkü o sayfa müşteriye
| karttan başka bir şey sormaz.
|
*/

$message = null;

/** @var array<string, list<string>> $errors */
$errors = [];

if (isSubmitted()) {
    try {
        $order = client()->orderPayment(new OrderPayment(
            channelReference: posted('channel_reference'),
            successUrl: posted('success_url'),
            customer: postedCustomer(),
            items: [new OrderItem(
                channelReference: posted('item_channel_reference'),
                name: posted('item_name') === '' ? null : posted('item_name'),
                quantity: posted('item_quantity') === '' ? null : (int) posted('item_quantity'),
                unitAmount: posted('item_unit_amount') === '' ? null : posted('item_unit_amount'),
            )],
            cancelUrl: posted('cancel_url') === '' ? null : posted('cancel_url'),
            description: posted('description') === '' ? null : posted('description'),
            currency: posted('currency') === '' ? null : posted('currency'),
        ));

        if ($order->result->successful) {
            header('Location: '.$order->checkoutUrl);

            exit;
        }

        $message = $order->result->message;
    } catch (ValidationException $exception) {
        $message = $exception->getMessage();
        $errors = $exception->errors;
    } catch (OdemehubException $exception) {
        $message = $exception->getMessage();
    }
}

pageStart('Ödeme sayfası');

echo '<p class="lead">Sipariş açılır ve müşteri, kartını geçidin kendi sayfasında girer.</p>';

notice($message);

sections([
    'Sipariş' => [
        'channel_reference' => ['Sipariş no', (string) random_int(1000, 9999)],
        'currency' => ['Para birimi (boş: TRY)', ''],
        'description' => ['Açıklama', 'Örnek sipariş'],
        'success_url' => ['Başarı adresi', callbackUrl()],
        'cancel_url' => ['Vazgeçme adresi', 'http://localhost:8080/index.php'],
    ],
    'Kalem' => [
        'item_channel_reference' => ['Ürün referansı', 'DENEME-1'],
        'item_name' => ['Ürün adı (boş: kayıtlı üründen)', 'Deneme ürünü'],
        'item_quantity' => ['Adet (boş: 1)', '1'],
        'item_unit_amount' => ['Birim fiyat (boş: kayıtlı üründen)', '120.00'],
    ],
    'Müşteri' => customerSection(),
], $errors, 'Ödeme sayfasını aç');

pageEnd();
