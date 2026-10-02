<?php

declare(strict_types=1);

require_once __DIR__.'/page.php';

/*
|--------------------------------------------------------------------------
| Örneklerin girişi
|--------------------------------------------------------------------------
|
| Geçidin yaptığı her iş için bir sayfa. İki ödeme türü aynı formu örnek
| verilerle açar; sipariş, abonelik ve ödeme linki kart sormaz, müşteriyi
| geçidin kendi ödeme sayfasına gönderir.
|
| Bu klasör `php -S localhost:8080 -t example` ile sunulur; hem 3D dönüşü
| hem de ödeme sayfasının dönüşü (config.php'deki callbackUrl) bu sunucuya
| POST edilir ve ikisini de return.php okur. webhook.php ise geçidin kendi
| sunucusundan gelen bildirimleri okur.
|
*/

pageStart('ödemehub örneği');

echo '<p class="lead">Ödeme geçidine gerçek bir istek atılır. Kimlik bilgileri ve kanal config.php dosyasındadır.</p>';

echo '<h2>Ödeme</h2><div class="actions">';
echo '<a class="button" href="regular.php">Doğrudan ödeme</a>';
echo '<a class="button" href="secure.php">3D Secure ödeme</a>';
echo '<a class="button" href="payments.php">Ödemeler</a>';
echo '</div>';

echo '<h2>Ödeme sayfası</h2><div class="actions">';
echo '<a class="button" href="order.php">Sipariş</a>';
echo '<a class="button" href="subscription.php">Abonelik</a>';
echo '<a class="button" href="link.php">Ödeme linki</a>';
echo '</div>';

echo '<h2>Sonrası</h2><div class="actions">';
echo '<a class="button" href="refund.php">İade / iptal</a>';
echo '<a class="button" href="cards.php">Kayıtlı kartlar</a>';
echo '<a class="button" href="bin.php">Kart sorgulama</a>';
echo '</div>';

pageEnd();
