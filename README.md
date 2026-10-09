# ödemehub PHP SDK

ödemehub ödeme geçidini kendi uygulamanızdan kullanmak için PHP istemcisi. Kart çekmek, 3D ödeme başlatmak, sipariş, abonelik ve ödeme linki açmak, kart saklamak, iade ve iptal yapmak, taksit sormak: hepsi burada.

İstemci her isteği gizli anahtarınızla imzalar, gelen her yanıtın imzasını doğrular. Siz imza, başlık ya da JSON ayrıntılarıyla uğraşmazsınız. Her uç nokta için bir metot vardır ve adı uç noktanın adıdır: `create-order` için `createOrder()`, `retrieve-saved-cards` için `retrieveSavedCards()`.

## Kurulum

PHP 8.2 ve üzeri gerekir.

```bash
composer require odemehub/php-sdk
```

## Yapılandırma

Üç bilgi gerekir. Hepsi paneldeki **Entegrasyon** sayfasındadır: Çalışma Alanı Kimliğiniz, API anahtarı ve gizli anahtar.

```php
use Gurmehub\Odemehub\Client;
use Gurmehub\Odemehub\Options;

$client = new Client(new Options(
    baseUrl: 'https://app.odemehub.com',
    team: '1000000001',                                   // Çalışma Alanı Kimliği
    apiKey: getenv('ODEMEHUB_API_KEY'),
    apiSecret: getenv('ODEMEHUB_API_SECRET'),
));
```

Gizli anahtar hiçbir zaman tel üzerinden gitmez; yalnızca imza üretmekte ve doğrulamakta kullanılır. Anahtarları kodun içine yazmayın.

Geçit hiçbir yerde veritabanı numarası kullanmaz: ödeme hesabı, işlem, sipariş, abonelik, link, link ödemesi ve kayıtlı kart her zaman UUID token'ıyla anılır.

`reference` sizin etiketinizdir, tekil olması gerekmez. `create-*` çağrıları (`createOrder`, `createSubscription`, `createPaymentLink`) her seferinde yeni bir kayıt ve yeni bir token açar; aynı referansı ikinci kez göndermek eski kaydı değiştirmez, hata da döndürmez. Böylece 3D'de vazgeçen ödeyeni aynı referansla yeniden ödemeye gönderebilirsiniz. Her create yanıtındaki token'ı saklayın: kaydı sonradan bu token'la güncellersiniz ve sorgularsınız. Referansla sorgu (`byReference()`) o referanstaki bütün kayıtları döner.

## İmza

Her istek üç başlıkla gider: `X-Api-Key`, `X-Timestamp` (Unix saniye) ve `X-Signature`. İmza, `"{timestamp}\n{METHOD}\n{path}\n{body}"` metni üzerinden gizli anahtarla alınan HMAC-SHA256'nın küçük harfli hex halidir. `path` adresin sorgu dizesiz yolu (`/api/1000000001/gateway/regular-payment`), `body` gönderilen JSON'ın kendisidir. Bütün uç noktalar POST'tur. Zaman damgası sunucu saatinden 5 dakikadan uzak olamaz. Geçit her yanıtı ve her webhook'u aynı yöntemle imzalar; istemci yanıtı isteğin metodu ve yoluyla, yanıtın kendi `X-Timestamp` değeriyle doğrular. Test vektörü `Gurmehub\Odemehub\Signature` sınıfının açıklamasındadır.

## Karttan doğrudan çekim

Müşteri hiçbir yere gitmez. Başarılı yanıt, paranın alındığı anlamına gelir.

```php
use Gurmehub\Odemehub\Request\{Address, Card, Customer, RegularPayment};

$customer = new Customer(
    reference: 'musteri-88',           // sizdeki müşteri anahtarı; kart saklanacaksa zorunlu, boşsa müşteri kaydedilmez
    billingAddress: new Address(
        firstname: 'Ahmet', lastname: 'Yılmaz',
        email: 'ahmet@ornek.com', phone: '05551112233',
        address: 'Kızılırmak Mah. Dumlupınar Blv. No:3',
        district: 'Çankaya', province: 'Ankara', country: 'TR',
    ),
);

$card = new Card(
    holderName: 'AHMET YILMAZ',
    number: '5400360000000003',
    expiryMonth: '12', expiryYear: '2030',
    securityCode: '000',
    shouldSave: true,                  // isteğe bağlı: başarılı ödemeden sonra kartı sakla
);

$payment = $client->regularPayment(new RegularPayment(
    reference: 'SIP-10231',            // sizdeki referans; en az bir rakam içermeli
    amount: '450.00',
    installmentNumber: 1,
    ip: $_SERVER['REMOTE_ADDR'],
    customer: $customer,
    card: $card,
));

if ($payment->result->successful) {
    $payment->transaction->token;      // iade ve iptalde ödeme bununla adlandırılır
    $payment->savedCard?->token;       // shouldSave gönderildiyse ve kart saklandıysa
}
```

Reddedilen ödeme de bir sonuçtur: `result->successful` false, `result->message` neden. Yalnızca geçit isteğin kendisini reddederse (hatalı alan, yetki, hız sınırı) istisna fırlatılır.

Kayıtlı kartla ödemede `card` yerine `savedCardToken` verilir; ödeme kartın saklandığı hesaptan geçer, `paymentProviderToken` gönderilmez. Kart hangi müşteri referansıyla saklandıysa ödeme de aynı referansı taşımalıdır.

**Müşteriler.** `customer.reference` gönderdiğiniz ödeme başarılı olunca geçit müşteriyi o referansla çalışma alanınızın müşteri listesine yazar ya da günceller; başarısız ödeme müşteriye dokunmaz. Referans göndermezseniz ödeme yine alınır ama müşteri kaydedilmez ve kart saklanamaz. Saklanan kart müşteriye bağlanır; müşterinin son ödeme yaptığı kart varsayılan kartı olur.

Tutarlar nokta ayraçlı ve en çok iki ondalıklı dizedir: `'100'`, `'100.1'`, `'100.10'`. Para birimi `Enum\Currency` ile verilir, boş bırakılırsa TRY'dir. Taksit yalnızca TRY'de 1'den büyük olabilir. Aynı anda satılan tutar ile çekilen tutar farklıysa (vade farkı) `baseAmount` verilir.

## 3D ödeme

Siz ödemeyi başlatırsınız, müşteri bankasına gider, banka müşteriyi sizin adresinize geri yollar.

```php
use Gurmehub\Odemehub\Request\SecurePayment;

$payment = $client->securePayment(new SecurePayment(
    reference: 'SIP-10232',
    amount: '450.00',
    installmentNumber: 1,
    ip: $_SERVER['REMOTE_ADDR'],
    customer: $customer,
    callbackUrl: 'https://magazam.com/odeme/donus',
    card: $card,
));

if ($payment->redirectUrl !== null) {
    header('Location: '.$payment->redirectUrl);           // müşteriyi bankaya gönderin
}
```

Başarılı yanıt ödeme alındı demek değildir; müşterinin gideceği adres hazır demektir. Adres 15 dakika geçerlidir ve bir kez açılır; süresinde açılmayan ödeme `expired` olur.

Banka işini bitirince müşterinin tarayıcısı `callbackUrl` adresinize şu alanları POST eder: `transaction_token`, `reference`, `successful` (`1`/`0`). Bu POST imzasızdır ve müşterinin tarayıcısından gelir; yalnızca ipucudur. Sonucu kendi imzalı bağlantınızdan sorun:

```php
use Gurmehub\Odemehub\Request\RetrievePayments;

$payment = $client->retrievePayments(RetrievePayments::byToken($_POST['transaction_token']))->payments[0] ?? null;

if ($payment?->isSuccessful()) {
    // siparişi ödendi olarak işaretleyin
}

$payment?->status;          // Enum\TransactionStatus::Successful, Failed, Expired ...
$payment?->paymentStatus;   // Enum\PaymentStatus: paranın akıbeti (sonradan Refunded olabilir)
$payment?->amount;          // karttan çekilen tutar
```

Geçidin ödeme yanıtları (`securePayment`, `regularPayment`, `refundPayment`, `cancelPayment`) ödemeyi `transaction` altında tam verir: `status`, `paymentStatus`, `securityType`, `amount`, `baseAmount`, `currency`, `installmentNumber`, `isTest`, `createdAt`, ve ödeme bir siparişte, linkte ya da abonelikte alındıysa `orderToken` / `paymentLinkToken` / `subscriptionToken`. Linkte alınan ödemede `paymentLinkToken` ile birlikte ödeyenin link ödemesini adlandıran `linkPaymentToken` da dolu gelir.

Müşteri hiç dönmezse panelde tanımladığınız webhook adresi yine de haber alır (`transaction.successful`, `transaction.failed`, `transaction.expired`). Aşağıya bakın.

## Sipariş

Kart sizde sorulmaz. Siparişi açarsınız, geçit kendi ödeme sayfasının adresini döner, müşteri orada öder. Tutar gönderilmez: geçit kalemleri ve seçilen gönderim yöntemini toplar. Birim tutarlar KDV dahildir.

```php
use Gurmehub\Odemehub\Request\{CreateOrder, Item};

$order = $client->createOrder(new CreateOrder(
    reference: 'SIP-10233',
    successUrl: 'https://magazam.com/odeme/donus',
    items: [
        new Item(name: 'Kulaklık', unitAmount: '1200.00', quantity: 1, taxRate: '20', reference: 'SKU-1', saveAsProduct: true),
    ],
    customer: $customer,               // bilinen kadarı; kalanı sayfada sorulur. Hiç verilmeyebilir.
    requiresShipping: true,            // ödeyen adresini ve panelinizdeki gönderim yöntemlerinden birini seçer
    cancelUrl: 'https://magazam.com/sepet',
    emailsCustomer: true,              // ödeme tamamlanınca fatura adresindeki e-postaya bilgilendirme gider; boş: gitmez
));

$order->order->token;              // saklayın: siparişi bundan sonra bununla güncellersiniz ve sorgularsınız
$order->order->checkoutUrl;        // müşteriyi buraya gönderin
$order->order->amount;             // geçidin hesapladığı toplam
```

**Kupon.** API'de kupon alanı yoktur; ödeyen kodu ödeme sayfasında girer. Kupon kullanılan siparişte `$order->discount` (`code`, `amount`) dolu gelir, kullanılmadıysa `null`'dır. Siparişin `subtotal`, `taxAmount` ve `amount` değerleri indirim düşülmüş tutarlardır; kupon gönderim ücretinden düşülmez.

**Müşteri kilidi.** `locksCustomer: true` gönderilirse ödeme sayfası müşteri bilgisi sormaz; gönderdiğiniz müşteriyi değiştirilemez şekilde gösterir ve ödemeyi onunla alır. Bu durumda fatura adresi eksiksiz olmalıdır; `requiresShipping: true` ise gönderim adresi de (gönderilmezse fatura adresi kullanılır). Eksik alan varsa geçit 422 ile reddeder. Yanıttaki `requiresShipping`, `locksCustomer` ve `emailsCustomer` kaydın bu ayarlarını verir.

`saveAsProduct: true` olan kalem referansıyla ürün listenize yazılır (referans zorunlu). Gönderim yöntemleri istekte gönderilmez: panelinizdeki **Gönderim Yöntemleri** listesinden ödeyenin adresine uyanlar sunulur, seçilen `$order->order->shippingMethod` olarak döner.

Ödendiğinde müşteri `successUrl` adresinize 3D dönüşüyle aynı alanlarla POST edilir; kesin sonucu `retrieveOrders()` verir; `order.paid` webhook'u geldiğinde de onu çağırın. `$order->transaction->paymentStatus` sonradan yapılan iadeyi gösterir.

```php
use Gurmehub\Odemehub\Request\{RetrieveOrders, UpdateOrder};

$order = $client->retrieveOrders(RetrieveOrders::byToken($token))->orders[0];
$order->isPaid();
$order->transaction?->token;
$order->customer?->reference;          // her siparişte müşterisi gelir

// Açık siparişte yalnızca gönderilen alanlar değişir; kalemler gönderilirse tamamı yenilenir.
$client->updateOrder(new UpdateOrder(token: $token, description: 'Hediye paketi', clear: ['cancel_url']));
```

Aynı referansla `createOrder()` yeniden çağrılırsa yeni bir sipariş ve yeni bir token açılır; önceki sipariş olduğu gibi kalır. Var olan siparişi değiştirmek için `updateOrder()` ile token'ını gönderin. Ödenmiş sipariş değişmez. **Bekleyen ödeme varken güncellenemez:** ödeme sayfasında son 15 dakika içinde başlamış bir ödeme varsa `updateOrder()` ve `updateSubscription()` çağrıları `token` alanında "bekleyen bir ödeme var, tamamlanmasını bekleyin" ile reddedilir. Link güncellemesi bundan etkilenmez.

## Ödeme linki

Link kim açarsa onun ödeyebileceği bir sayfadır; kapatılana ya da son gününe kadar tekrar tekrar ödenir. Müşterisi yoktur. Her `createPaymentLink()` çağrısı yeni bir link ve yeni bir token açar; linki sonradan token'ıyla güncellersiniz.

```php
use Gurmehub\Odemehub\Enum\Currency;
use Gurmehub\Odemehub\Request\{CreatePaymentLink, RetrievePaymentLinks, UpdatePaymentLink};

$link = $client->createPaymentLink(new CreatePaymentLink(
    currency: Currency::TRY,
    items: [new Item(name: 'Bağış', unitAmount: '100.00', quantity: 1, taxRate: '0')],
    reference: 'LNK-1',                 // boş bırakılırsa geçit LINK{n} üretir
    expiresAt: '2026-12-31',            // çalışma alanının saat dilimine göre gün
    emailsCustomer: true,               // ödeme tamamlanınca ödeyene, sayfada verdiği adrese e-posta gider; boş: gitmez
));

$link->paymentLink->token;              // saklayın: linki bununla güncellersiniz ve sorgularsınız
$link->paymentLink->checkoutUrl;        // linkin kendisi; ödenemezken (kapalı, süresi geçmiş) null
$link->paymentLink->expiresAt;          // verilen günün sonu (çalışma alanı saatiyle), ISO 8601 UTC
$link->paymentLink->isTest;             // ödemeleri şu an test ortamında mı alınıyor

$detail = $client->retrievePaymentLinks(RetrievePaymentLinks::byToken($link->paymentLink->token))->paymentLinks[0];
$detail->transactions;                  // son 50 deneme, yeniden eskiye
$detail->transactionsCount;             // linkteki denemelerin tamamının sayısı
$detail->successful();                  // listelenenlerden başarılı olanlar

$client->updatePaymentLink(new UpdatePaymentLink(token: $link->paymentLink->token, isActive: false));
```

**Tutarı ödeyen seçer.** `amountType` (`Enum\AmountType`) linkte ne ödeneceğini söyler: `Fixed` (varsayılan) kalemlerin toplamı; `Custom` ödeyenin yazdığı tutar; `Predefined` `predefinedAmounts` içinden biri (en çok 10); `PredefinedAndCustom` ikisinden biri. `Fixed` dışındaki tiplerde ödeme `itemName` adında tek kalem olarak alınır, `items` gönderilmez (gönderilirse yok sayılır). `taxRate` ödenen tutardaki vergi oranıdır; `taxMode` (`Enum\TaxMode`) `Inclusive` (varsayılan) ise vergi tutarın içinden ayrılır, `Exclusive` ise üstüne eklenir. `currencyType: CurrencyType::Selectable` ile ödeyen `currencies` içinden para birimini seçer; `currency` başlangıçta seçili olandır ve her zaman listede yer alır.

```php
use Gurmehub\Odemehub\Enum\{AmountType, Currency, CurrencyType, TaxMode};

$donation = $client->createPaymentLink(new CreatePaymentLink(
    currency: Currency::TRY,
    amountType: AmountType::PredefinedAndCustom,
    itemName: 'Bağış',
    predefinedAmounts: ['100', '250', '500'],
    taxRate: '0',
    taxMode: TaxMode::Inclusive,
    currencyType: CurrencyType::Selectable,
    currencies: [Currency::USD, Currency::EUR],
));

$donation->paymentLink->amount;             // null: tutarı ödeyen seçer (subtotal ve taxAmount da null)
$donation->paymentLink->predefinedAmounts;  // ['100.00', '250.00', '500.00']
$donation->paymentLink->currencies;         // [Currency::TRY, Currency::USD, Currency::EUR]
```

Hangi alanın hangi tipte zorunlu olduğunu geçit denetler; eksik alan `ValidationException` (422) ile döner. Güncellemede `clear` listesi `description`, `expires_at`, `payment_provider_token`, `item_name`, `predefined_amounts`, `tax_rate` ve `currencies` alanlarını boşaltabilir. `Fixed` tipe dönen ya da `Fixed` kalan link kalemsiz kalamaz.

**Link ödemeleri.** Linkte yapılan her ödeme bir link ödemesidir (`LINKPAY1`, `LINKPAY2`…); ödeyen açar, siz yalnızca sorgularsınız:

```php
use Gurmehub\Odemehub\Request\RetrieveLinkPayments;

$payments = $client->retrieveLinkPayments(RetrieveLinkPayments::between('2026-09-26', '2026-10-02'));
foreach ($payments->linkPayments as $linkPayment) {
    $linkPayment->paymentLink->token;                   // ödendiği link
    $linkPayment->status;                               // Enum\LinkPaymentStatus: Open, Paid
    $linkPayment->isPaid();
    $linkPayment->amount;                               // çekilen tutar, kupon düşülmüş
    $linkPayment->discount?->code;                      // ödeyen kupon girdiyse
    $linkPayment->customer?->billingAddress->email;     // ödeyenin fatura bilgileri
    $linkPayment->transaction?->token;                  // ödeyen işlem; iade ve iptal bununla
}
```

Panelden açtığınız linkler de aynı uçlarla, token'ı ya da referansıyla bulunur. Linkle ödeyen kişi müşteri listenize yazılmaz ve kartı saklanmaz.

## Abonelik

İlk yenileme ödeme sayfasında ödenir ve kart orada müşteriye saklanır; sonrakiler müşterinin varsayılan kartından çekilir. Müşteri referansı zorunludur. Hesap kart saklamalı ve 3D ödeme almalıdır.

```php
use Gurmehub\Odemehub\Enum\{Period, SubscriptionStatus};
use Gurmehub\Odemehub\Request\{CreateSubscription, RetrieveSubscriptions, UpdateSubscription};

$subscription = $client->createSubscription(new CreateSubscription(
    reference: 'ABO-1',
    period: Period::Monthly,
    successUrl: 'https://magazam.com/abonelik/donus',
    items: [new Item(name: 'Premium', unitAmount: '99.90', quantity: 1, taxRate: '20')],
    customer: $customer,
    renewalLimit: 12,                   // boş: iptale kadar
    emailsCustomer: true,               // her durum değişiminde fatura adresindeki e-postaya bilgilendirme gider
));

$subscription->subscription->checkoutUrl;

$current = $client->retrieveSubscriptions(RetrieveSubscriptions::byToken($token))->subscriptions[0];
$current->status;                        // Enum\SubscriptionStatus: Pending, Active, PastDue, Cancelled, Completed
$current->renewal->paidAt;               // içinde bulunulan yenileme
$current->nextPaymentAt;
$current->customer?->reference;

// Dönem, kalemler, ödeme sayısı değişir; iptal de buradan:
$client->updateSubscription(new UpdateSubscription(token: $token, status: SubscriptionStatus::Cancelled));
```

İptalde para iade edilmez; ödenmiş dönem sonuna kadar sürer, sonra abonelik biter (`subscription.ended`). Ödenmiş dönem yoksa hemen `cancelled` olur.

Kupon yalnızca ilk ödemede, ödeme sayfasında girilir. `$current->discount` (`code`, `amount`) o kuponu verir, yoksa `null`'dır. Aboneliğin kendi `subtotal` / `taxAmount` / `amount` değerleri indirimsizdir; ilk dönemde gerçekten çekilen tutar `renewal->amount`'tadır.

`locksCustomer` siparişteki gibi çalışır. `emailsCustomer` açıkken dönem ödemesi alınamazsa ödeme sayfasının bağlantısı doğrudan müşteriye gider, size ayrıca e-posta gelmez.

İlk ödemeden sonra yalnızca iptal (`status`), ödeme sayısı (`renewalLimit`, ödenenden az olamaz), dönem (`period`) ve aynı kalemlerin birim fiyatı değişebilir; müşteri dahil başka bir alan gönderilirse geçit 422 ile reddeder.

## Kayıtlı kartlar

Kart ödeme sırasında (`shouldSave: true`) ya da ödemesiz saklanır; ikisinde de `customer.reference` zorunludur. Kart o müşteriye bağlanır ve müşteri referansıyla bulunur; kart yanıtlarındaki `customer` yalnızca `reference` taşır. Ödemesiz saklamada müşteri, sağlayıcı kartı kabul edince gönderdiğiniz bilgilerle listenize yazılır.

```php
use Gurmehub\Odemehub\Request\{CreateSavedCard, DeleteSavedCard, RetrieveSavedCards, UpdateSavedCard};

$saved = $client->createSavedCard(new CreateSavedCard(customer: $customer, card: $card));
$saved->savedCard?->token;              // sağlayıcı saklamadıysa null, nedeni result->message

$cards = $client->retrieveSavedCards(RetrieveSavedCards::byReference('musteri-88'));
$cards->default();                      // varsayılan kart, varsa

$client->updateSavedCard(new UpdateSavedCard($cardToken));     // varsayılan yap
$client->deleteSavedCard(new DeleteSavedCard($cardToken));     // delete-saved-card/{token}
```

Kayıtlı kart yanıtlarında kartın yalnızca ilk haneleri ve son dördü vardır. `Request\Card` nesnesi `var_dump` ve benzeri çıktılarda numarayı ve CVV'yi `*****` olarak gösterir.

## İade ve iptal

Ödemenin token'ı yeter. İade, sağlayıcının kapattığı ödemeden kısmen ya da tamamen; iptal, henüz kapatılmamış ödemenin tamamı.

```php
use Gurmehub\Odemehub\Request\{CancelPayment, RefundPayment};

$refund = $client->refundPayment(new RefundPayment(token: $transactionToken, amount: '50.00'));   // tutar boş: kalanın tamamı
$refund->refund->amount;                // gerçekten geri giden tutar

$cancel = $client->cancelPayment(new CancelPayment(token: $transactionToken));
```

## Kart sorgusu ve taksitler

Kartın ilk 6–8 hanesiyle bankası, tipi ve tutara göre taksit seçenekleri. Hiçbir şey çekilmez.

```php
use Gurmehub\Odemehub\Request\RetrieveBin;

$bin = $client->retrieveBin(new RetrieveBin(bin: '415565', amount: '1200.00'));

$bin->issuerName;                       // Yapı Kredi
$bin->scheme;                           // Enum\CardScheme::Visa
foreach ($bin->installments as $installment) {
    $installment->number;               // 3
    $installment->total;                // 1236.00
}
```

## Sorgulama

Her kaynak tek bir `retrieve-*` ucuyla sorulur ve yanıt her zaman bir listedir (eskiden yeniye); eşleşen yoksa boş liste döner. Kayıt üç yoldan biriyle adlandırılır: token'ı, sizdeki referansı ya da açıldığı günler. Gün aralığı en çok 7 gündür ve çalışma alanının saat dilimindedir; hiçbiri verilmezse son 7 gün.

```php
use Gurmehub\Odemehub\Request\RetrievePayments;

// Yanıtı alınamayan bir ödemenin akıbeti: referanstaki bütün denemeler
$client->retrievePayments(RetrievePayments::byReference('SIP-10231'));

// Belli günlerdeki bütün denemeler, reddedilenler dahil, durumu ve tutarıyla
$list = $client->retrievePayments(RetrievePayments::between('2026-09-26', '2026-10-02'));
foreach ($list->payments as $transaction) {
    $transaction->status;               // Enum\TransactionStatus; Timeout: sağlayıcı yanıt vermedi
    $transaction->paymentStatus;        // Enum\PaymentStatus: Paid, Refunded, PartiallyRefunded...
}

$client->retrievePayments(RetrievePayments::latest());   // son 7 gün
```

Aynısı `retrieveOrders` (`RetrieveOrders`), `retrieveSubscriptions` (`RetrieveSubscriptions`), `retrievePaymentLinks` (`RetrievePaymentLinks`), `retrieveLinkPayments` (`RetrieveLinkPayments`; referans link ödemesinin `LINKPAY{n}` numarasıdır) ve `retrieveSavedCards` (`RetrieveSavedCards`; referans müşterinin referansıdır) için de geçerlidir. Referans tekil olmadığından `byReference()` o referanstaki bütün kayıtları döner.

## Webhook

Sipariş ödendiğinde, link ödemesi alındığında, abonelik durum değiştirdiğinde, API ödemesi bittiğinde ve bir ödeme iade ya da iptal edildiğinde geçit imzalı JSON POST eder. Adresler kodda verilmez; panelde **Ayarlar → Webhook** sayfasında olay ve adres seçilerek tanımlanır.

Olaylar (`Enum\WebhookEvent`):

| Kaynak | Olaylar |
| --- | --- |
| Sipariş | `order.paid`, `order.payment_refunded`, `order.payment_cancelled` |
| Ödeme linki | `payment_link.paid`, `payment_link.payment_refunded`, `payment_link.payment_cancelled` |
| Abonelik | `subscription.active`, `subscription.past_due`, `subscription.cancelled`, `subscription.ended`, `subscription.completed`, `subscription.payment_refunded`, `subscription.payment_cancelled` |
| API ödemesi | `transaction.successful`, `transaction.failed`, `transaction.expired`, `transaction.payment_refunded`, `transaction.payment_cancelled` |

Sipariş, link ya da abonelikte alınan ödeme için `transaction.*` gelmez; o kaynağın kendi olayı gelir.

**Webhook nihai sonuç değildir.** Gövde yalnızca kaynağın token'ını (para hareketi varsa yanında ödemenin token'ını; `payment_link.*` olaylarında ayrıca link ödemesinin token'ını) taşır; `discount` gibi ayrıntılar gövdede yoktur. Kararı, token ile geçide sorduğunuz yanıta göre verin ve yanıtı kendi kaydınızla (referans, tutar, durum) karşılaştırın:

```php
use Gurmehub\Odemehub\Exception\SignatureException;
use Gurmehub\Odemehub\Request\{RetrieveLinkPayments, RetrieveOrders, RetrievePayments, RetrieveSubscriptions};

try {
    $webhook = $client->webhook(
        $_SERVER['REQUEST_METHOD'],
        parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
        file_get_contents('php://input'),
        $_SERVER['HTTP_X_TIMESTAMP'] ?? null,
        $_SERVER['HTTP_X_SIGNATURE'] ?? null,
    );
} catch (SignatureException $e) {
    http_response_code(401);
    exit;
}

$webhook->id;      // aynı bildirim tekrar gelebilir; bununla ayıklayın
$webhook->event;   // order.paid, subscription.active ...

if ($webhook->orderToken !== null) {
    $order = $client->retrieveOrders(RetrieveOrders::byToken($webhook->orderToken))->orders[0];
    $order->status;                         // Enum\OrderStatus::Paid
    $order->transaction?->paymentStatus;    // Enum\PaymentStatus: Refunded, PartiallyRefunded ...
} elseif ($webhook->linkPaymentToken !== null) {   // payment_link.*
    $linkPayment = $client->retrieveLinkPayments(RetrieveLinkPayments::byToken($webhook->linkPaymentToken))->linkPayments[0];
    $linkPayment->paymentLink->token;       // $webhook->paymentLinkToken ile aynı
    $linkPayment->isPaid();
    $linkPayment->transaction?->paymentStatus;
} elseif ($webhook->subscriptionToken !== null) {
    $subscription = $client->retrieveSubscriptions(RetrieveSubscriptions::byToken($webhook->subscriptionToken))->subscriptions[0];
} elseif ($webhook->transactionToken !== null) {   // transaction.*
    $transaction = $client->retrievePayments(RetrievePayments::byToken($webhook->transactionToken))->payments[0];
}

http_response_code(204);
```

Sipariş, link ve abonelikte para hareketi olan olaylarda `transactionToken` da gelir; `retrievePayments()` yanıtındaki `orderToken` / `paymentLinkToken` / `linkPaymentToken` / `subscriptionToken` ödemenin gerçekten o kaynağa ait olduğunu gösterir.

Yalnızca doğrulamak için `$client->verifyWebhook(...)` `bool` döner. Adres üretimde https ve herkese açık olmalıdır; geçit 2xx yanıt alana kadar 60 sn, 5 dk, 15 dk ve 30 dk arayla toplam 5 kez dener. Yönlendirmeleri izlemez.

## Hatalar

Hepsi `Gurmehub\Odemehub\Exception\OdemehubException` türündendir.

| İstisna | Durum | Anlamı |
| --- | --- | --- |
| `AuthenticationException` | 401 | API anahtarı yanlış, imza tutmuyor ya da zaman damgası aralık dışında |
| `ForbiddenException` | 403 | Çalışma alanı işlem yapamıyor (ödenmemiş bakiye, plan) ya da plan bu özelliği kapsamıyor |
| `NotFoundException` | 404 | Güncellenmek, silinmek, iade ya da iptal edilmek istenen kayıt yok (sorgularda boş liste döner) |
| `ValidationException` | 422 | Alan hataları; `$e->errors` noktalı alan adıyla (`transaction.amount`, `order.items.0.name`) |
| `RateLimitException` | 429 | İstek sınırı; `$e->retryAfter` saniye |
| `SignatureException` | — | Yanıtın ya da webhook'un imzası doğrulanamadı; içeriğe güvenmeyin |
| `TransportException` | — | Geçide ulaşılamadı; ödemenin akıbetini `retrievePayments(RetrievePayments::byReference(...))` ile sorun |
| `UnexpectedResponseException` | 500, diğer | Geçitte beklenmeyen hata ya da okunamayan yanıt; `$e->status` |

```php
use Gurmehub\Odemehub\Exception\{OdemehubException, ValidationException};

try {
    $payment = $client->regularPayment($request);
} catch (ValidationException $e) {
    $e->errors['transaction.amount'][0] ?? null;
} catch (OdemehubException $e) {
    $e->getMessage();                   // Türkçe
}
```

Reddedilen ödeme, iade ya da kart saklama istisna değildir; `result->successful` false ve `result->message` dolu döner.

## İstek sınırları

Sınırlar çalışma alanı başına ve dakikalıktır:

| Sınır | Kapsam |
| --- | --- |
| 300 istek / dk | bütün uç noktalar |
| 60 istek / dk | `secure-payment`, `regular-payment`, `refund-payment`, `cancel-payment`, `create-saved-card`, `delete-saved-card` (300'e ek olarak) |

Aşıldığında 429 ve `RateLimitException` döner; `retryAfter` kadar bekleyip aynı isteği yeniden gönderin.

## Değişiklikler

### 1.0.3

Eklenenler:

- **Müşteri kilidi ve müşteriye e-posta:** `CreateOrder`, `UpdateOrder`, `CreateSubscription` ve `UpdateSubscription` `locksCustomer` ve `emailsCustomer` alır. `Order` ve `Subscription` yanıtları `requiresShipping`, `locksCustomer` ve `emailsCustomer` taşır.

Kırıcı değişiklik:

- **Ödeme linkinde `emailsPayer` → `emailsCustomer`.** `CreatePaymentLink`, `UpdatePaymentLink` ve `PaymentLink` yanıtında alanın adı değişti; geçit eski `emails_payer` adını artık kabul etmez. `emailsPayer:` yazan kod `emailsCustomer:` olarak değişmelidir.
- **`UpdateOrder` ve `UpdateSubscription` yapıcısında yeni alanlar `clear`'dan önce gelir.** `clear`'ı konumsal argümanla geçen kod isimli argümana geçmelidir.

### 1.0.2

Eklenenler:

- **`retrieveLinkPayments()`** (`retrieve-link-payments`): linkte yapılan ödemeler, öteki sorgular gibi `RetrieveLinkPayments::byToken()`, `byReference()` (`LINKPAY{n}`), `between()` ya da `latest()` ile. Yanıt `LinkPaymentList`; her `LinkPayment` `token`, `reference`, `paymentLink` (`token`, `reference`), `paymentProviderToken`, `status`, `items`, `subtotal`, `taxAmount`, `amount`, `discount`, `currency`, `customer` (`billingAddress`), `isTest`, `createdAt` ve `transaction` taşır.
- **`linkPaymentToken`**: `PaymentTransaction` (ödeme yanıtlarındaki `transaction`), `Transaction` (`retrievePayments()` ve `retrievePaymentLinks()` içindeki denemeler) ve `Webhook` (bütün `payment_link.*` olayları) linkte alınan ödemede link ödemesinin token'ını verir.
- **Ödeme linkinde seçimli tutar ve para birimi:** `CreatePaymentLink` ve `UpdatePaymentLink` yeni alanlar alır: `amountType`, `itemName`, `predefinedAmounts`, `taxRate`, `taxMode`, `currencyType`, `currencies`, `emailsPayer`. `PaymentLink` yanıtı aynı alanları taşır. `UpdatePaymentLink` `clear` listesi artık `item_name`, `predefined_amounts`, `tax_rate` ve `currencies` alanlarını da boşaltabilir.
- **`discount`**: `Order`, `Subscription` ve `LinkPayment` ödeyenin ödeme sayfasında girdiği kuponu `Discount` (`code`, `amount`) olarak taşır; kupon yoksa `null`. Webhook gövdesinde yoktur.
- **Yeni enum'lar:** `Enum\LinkPaymentStatus` (`Open`, `Paid`), `Enum\AmountType` (`Fixed`, `Custom`, `Predefined`, `PredefinedAndCustom`), `Enum\CurrencyType` (`Fixed`, `Selectable`), `Enum\TaxMode` (`Inclusive`, `Exclusive`). Bilinmeyen değer, öteki enum'larda olduğu gibi `null` okunur.

Davranış ve küçük kırıcı değişiklikler:

- **`create-*` artık idempotent değil.** `createOrder()`, `createSubscription()` ve `createPaymentLink()` aynı `reference` ile çağrılsa da her seferinde yeni kayıt ve yeni token açar; eski kayıt güncellenmez, 422 de dönmez. Referans tekil değildir. Her create yanıtındaki token'ı saklayın ve değişikliği `update*()` ile token'la yapın.
- **Link güncellemesi bekleyen ödemede reddedilmez.** Bekleyen ödeme engeli yalnızca `updateOrder()` ve `updateSubscription()` için geçerlidir.
- **`CreatePaymentLink` yapıcısında `currency` artık ilk parametre, `items` isteğe bağlı** (`Fixed` dışındaki tiplerde gönderilmez). İsimli argümanla yazılmış kod etkilenmez; konumsal argümanla `items, currency` sırasında çağıran kod isimli argümana geçmelidir.
- **`UpdatePaymentLink` yapıcısında yeni alanlar `clear`'dan önce gelir.** `clear`'ı konumsal argümanla geçen kod isimli argümana geçmelidir.
- **`PaymentLink::$subtotal`, `$taxAmount`, `$amount` artık `?string`.** Tutarı ödeyenin seçtiği linkte `null` gelir (eskiden boş dize okunuyordu).

## 1.0.1'deki kırıcı değişiklikler

1.0.1, SDK'yı geçidin bugünkü API'sine taşır ve 1.0.0 koduyla uyumlu değildir. 1.0.0'dan geçerken dikkat edilecekler:

- **Uç noktalar yeniden adlandırıldı.** Her metot uç noktanın adını taşır: `orderPayment()` → `createOrder()`, `subscriptionPayment()` → `createSubscription()`, `saveCard()` → `createSavedCard()`, `savedCards()` → `retrieveSavedCards()`, `defaultSavedCard()` → `updateSavedCard()`, `cancelSubscription()` → `updateSubscription(status: SubscriptionStatus::Cancelled)`, `retrieveTransactions()` → `retrievePayments()`. `saveProduct()` kalktı; kalemler isteğin içinde gönderilir.
- **Tekil kaydı adlandıran istek alanı her yerde `token`.** `RefundPayment` ve `CancelPayment` artık `transactionToken` değil `token` alır.
- **Yanıtlar API JSON'unu birebir yansıtır.** Ödeme yanıtında düz `transactionToken` / `channelToken` / `channelReference` yerine `$payment->transaction->token` vb.; `transaction` artık `status`, `paymentStatus`, `securityType`, `amount`, `baseAmount`, `currency`, `installmentNumber`, `isTest`, `createdAt` de taşır. Müşteri `$payment->customer` altındadır.
- **Sabit kümeli alanlar tipli.** Yanıtlarda `currency`, `period`, `status` (sipariş, abonelik, işlem), `paymentStatus`, `securityType`, `refund->type`, kart `scheme` ve `type` artık dize değil `Gurmehub\Odemehub\Enum\*` değerleridir (`?Currency`, `?OrderStatus`, `?SubscriptionStatus`, `?TransactionStatus`, `?PaymentStatus`, `?SecurityType`, `?RefundType`, `?CardScheme`, `?CardType`, `?Period`). Karşılaştırmayı enum'la yapın (`$order->status === OrderStatus::Paid`), metin gerekiyorsa `->value` kullanın. Geçit SDK'nın bilmediği yeni bir değer gönderirse alan çökmeden `null` olur.
- **Müşteri:** `OrderDetails` / `SubscriptionDetails` müşteriyi hem üstte (`->customer`) hem varlığın üzerinde (`->order->customer`, `->subscription->customer`) taşır; listelerde her öğenin `customer` alanındadır.
- **Kayıtlı kartlar** müşteri referansıyla tutulur: `RetrieveSavedCards` `email` almaz, kart yanıtlarındaki `customer` yalnızca `reference` taşır. `deleteSavedCard()` `delete-saved-card/{token}` adresine gider.
- **Ödeme linki:** `checkoutUrl` link ödenemezken `null`; `expiresAt` ISO 8601 UTC; `retrievePaymentLinks()` son 50 denemeyi ve `transactionsCount`'u döner.
- **Hatalar:** güncellenen, silinen, iade ya da iptal edilen kaydın token'ı bulunamazsa `NotFoundException` (404) atılır; eskiden bu durum 422 dönüyordu. Sorgular bulunamayan kayıtta boş liste döner. Yeni istisnalar: `ForbiddenException` (403), `NotFoundException` (404), `RateLimitException` (429, `retryAfter`).
- **Webhook:** `orderWebhook()`, `subscriptionWebhook()`, `transactionWebhook()` yerine tek `webhook()` (ve `verifyWebhook()`); imza yöntem, yol, gövde ve zaman damgası üzerindendir. Gövde artık yalnızca token taşır (`orderToken`, `paymentLinkToken`, `subscriptionToken`, `transactionToken`); durum `retrieve*()` ile sorulur. Adresler panelde tanımlandığı için `SecurePayment`, `CreateOrder`, `UpdateOrder`, `CreateSubscription`, `UpdateSubscription` artık `webhookUrl` almaz.
- **Kanal kalktı.** `Options` artık `channelToken` almaz; isteklerde `channelToken` yoktur. Referans alanları `channelReference` yerine `reference` adını taşır (ödeme, sipariş, abonelik, link, kalem); yanıtlarda `channelToken` yoktur. Geri dönüşte tarayıcı `channel_reference` değil `reference` POST eder.
- **Sorgular tek uçta.** Her kaynakta tek sorgu metodu vardır: `retrievePayments()`, `retrieveOrders()`, `retrieveSubscriptions()`, `retrievePaymentLinks()`, `retrieveSavedCards()`. İstek `byToken()`, `byReference()`, `between()` ya da `latest()` ile kurulur; yanıt her zaman listedir, bulunamayan kayıt 404 değil boş listedir.
- **Gönderim:** sipariş ve abonelik `requiresShipping` ile ödeme sayfasında gönderim adresi ister; gönderim yöntemleri panelde tanımlanır, istekte gönderilmez. Yanıtta yalnızca ödeyenin seçtiği yöntem (`shippingMethod`: `reference`, `title`, `amount`, `taxRate`) gelir.
- **Kalemler:** `taxRate` isteğe bağlı (boşsa vergisiz); yeni `saveAsProduct`.
- **Müşteri:** referans gönderilmeyebilir; o zaman müşteri kaydedilmez ve kart saklanamaz. Abonelikte ve kart saklamada zorunludur. `NamedCustomer` `reference` ve `billingAddress` alanları boş olabilir.
- **Ödeme linki:** `retrievePaymentLinks()` her linkte son 50 denemeyi ve `transactionsCount`'u `PaymentLink` üzerinde verir; `PaymentLinkDetails` yalnızca linki taşır.
- **Kayıtlı kart:** listede her kart kendi `customer`'ını taşır; `SavedCardList::$customer` kalktı. Ödeme yanıtındaki `Transaction::$savedCard`, kartın saklanması istendiyse saklanan kartı verir.

## Örnekler

`example/` klasöründe her uç nokta grubu için çalışan bir sayfa vardır. `php -S localhost:8080 -t example` ile sunun; kimlik bilgileri `example/config.php` içindedir ve ortam değişkenleriyle değiştirilir.
