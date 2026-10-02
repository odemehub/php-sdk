# ödemehub PHP SDK

ödemehub ödeme geçidini kendi uygulamanızdan kullanmak için PHP istemcisi. Kart çekmek, 3D ödeme başlatmak, sipariş, abonelik ve ödeme linki açmak, kart saklamak, iade ve iptal yapmak, taksit sormak: hepsi burada.

İstemci her isteği gizli anahtarınızla imzalar, gelen her yanıtın imzasını doğrular. Siz imza, başlık ya da JSON ayrıntılarıyla uğraşmazsınız. Her uç nokta için bir metot vardır ve adı uç noktanın adıdır: `create-order` için `createOrder()`, `retrieve-saved-cards-by-reference` için `retrieveSavedCardsByReference()`.

## Kurulum

PHP 8.2 ve üzeri gerekir.

```bash
composer require odemehub/php-sdk
```

## Yapılandırma

Dört bilgi gerekir. Hepsi paneldeki **Entegrasyon** sayfasındadır: Çalışma Alanı Kimliğiniz, API anahtarı, gizli anahtar ve kanalınızın token'ı.

```php
use Gurmehub\Odemehub\Client;
use Gurmehub\Odemehub\Options;

$client = new Client(new Options(
    baseUrl: 'https://app.odemehub.com',
    team: '1000000001',                                   // Çalışma Alanı Kimliği
    channelToken: '6f1c2e7a-4b3d-4c8e-9a61-2f5d7b0c3e14', // müşterinin size ulaştığı kanal
    apiKey: getenv('ODEMEHUB_API_KEY'),
    apiSecret: getenv('ODEMEHUB_API_SECRET'),
));
```

Gizli anahtar hiçbir zaman tel üzerinden gitmez; yalnızca imza üretmekte ve doğrulamakta kullanılır. Anahtarları kodun içine yazmayın.

Kanal token'ı entegrasyon için bir kez verilir ve her isteğe istemci yazar. Birden çok kanalda satıyorsanız tek bir istekte `channelToken` vererek o isteği başka kanala yazdırabilirsiniz. Geçit hiçbir yerde veritabanı numarası kullanmaz: kanal, ödeme hesabı, işlem, sipariş, abonelik, link ve kayıtlı kart her zaman UUID token'ıyla anılır.

## İmza

Her istek üç başlıkla gider: `X-Api-Key`, `X-Timestamp` (Unix saniye) ve `X-Signature`. İmza, `"{timestamp}\n{METHOD}\n{path}\n{body}"` metni üzerinden gizli anahtarla alınan HMAC-SHA256'nın küçük harfli hex halidir. `path` adresin sorgu dizesiz yolu (`/api/1000000001/gateway/regular-payment`), `body` gönderilen JSON'ın kendisidir; GET isteklerinde boş dizedir. Zaman damgası sunucu saatinden 5 dakikadan uzak olamaz. Geçit her yanıtı ve her webhook'u aynı yöntemle imzalar; istemci yanıtı isteğin metodu ve yoluyla, yanıtın kendi `X-Timestamp` değeriyle doğrular. Test vektörü `Gurmehub\Odemehub\Signature` sınıfının açıklamasındadır.

## Karttan doğrudan çekim

Müşteri hiçbir yere gitmez. Başarılı yanıt, paranın alındığı anlamına gelir.

```php
use Gurmehub\Odemehub\Request\{Address, Card, Customer, RegularPayment};

$customer = new Customer(
    reference: 'musteri-88',           // sizdeki müşteri anahtarı; kart saklanacaksa zorunlu
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
    channelReference: 'SIP-10231',     // sizdeki referans; en az bir rakam içermeli
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

Kayıtlı kartla ödemede `card` yerine `savedCardToken` verilir; ödeme kartın saklandığı hesaptan geçer, `paymentProviderToken` gönderilmez. Kart hangi kanal, müşteri referansı ve e-postayla saklandıysa ödeme de aynılarını taşımalıdır.

Tutarlar nokta ayraçlı ve en çok iki ondalıklı dizedir: `'100'`, `'100.1'`, `'100.10'`. Para birimi `Enum\Currency` ile verilir, boş bırakılırsa TRY'dir. Taksit yalnızca TRY'de 1'den büyük olabilir. Aynı anda satılan tutar ile çekilen tutar farklıysa (vade farkı) `baseAmount` verilir.

## 3D ödeme

Siz ödemeyi başlatırsınız, müşteri bankasına gider, banka müşteriyi sizin adresinize geri yollar.

```php
use Gurmehub\Odemehub\Request\SecurePayment;

$payment = $client->securePayment(new SecurePayment(
    channelReference: 'SIP-10232',
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

Banka işini bitirince müşterinin tarayıcısı `callbackUrl` adresinize şu alanları POST eder: `transaction_token`, `channel_reference`, `successful` (`1`/`0`). Bu POST imzasızdır ve müşterinin tarayıcısından gelir; yalnızca ipucudur. Sonucu kendi imzalı bağlantınızdan sorun:

```php
use Gurmehub\Odemehub\Request\RetrievePayment;

$outcome = $client->retrievePayment(new RetrievePayment($_POST['transaction_token']));

if ($outcome->result->successful) {
    // siparişi ödendi olarak işaretleyin
}

$outcome->transaction->status;          // Enum\TransactionStatus::Successful, Failed, Expired ...
$outcome->transaction->paymentStatus;   // Enum\PaymentStatus: paranın akıbeti (sonradan Refunded olabilir)
$outcome->transaction->amount;          // karttan çekilen tutar
```

Geçidin kendi ödeme yanıtları (`securePayment`, `regularPayment`, `refundPayment`, `cancelPayment`, `retrievePayment`, `retrievePaymentByReference`) ödemeyi `transaction` altında tam verir: `status`, `paymentStatus`, `securityType`, `amount`, `baseAmount`, `currency`, `installmentNumber`, `isTest`, `createdAt`, ve ödeme bir siparişte, linkte ya da abonelikte alındıysa `orderToken` / `paymentLinkToken` / `subscriptionToken`.

Müşteri hiç dönmezse panelde kanala tanımladığınız webhook adresi yine de haber alır (`transaction.successful`, `transaction.failed`, `transaction.expired`). Aşağıya bakın.

## Sipariş

Kart sizde sorulmaz. Siparişi açarsınız, geçit kendi ödeme sayfasının adresini döner, müşteri orada öder. Tutar gönderilmez: geçit kalemleri ve seçilen gönderim yöntemini toplar. Birim tutarlar KDV dahildir.

```php
use Gurmehub\Odemehub\Request\{CreateOrder, Item, ShippingMethod};

$order = $client->createOrder(new CreateOrder(
    channelReference: 'SIP-10233',
    successUrl: 'https://magazam.com/odeme/donus',
    items: [
        new Item(name: 'Kulaklık', unitAmount: '1200.00', quantity: 1, taxRate: '20', channelReference: 'SKU-1'),
    ],
    customer: $customer,                                   // bilinen kadarı; kalanı sayfada sorulur
    shippingMethods: [
        new ShippingMethod(handle: 'standart', title: 'Standart Kargo', amount: '49.90', taxRate: '20'),
    ],
    requiresShippingAddress: true,
    cancelUrl: 'https://magazam.com/sepet',
));

$order->order->checkoutUrl;        // müşteriyi buraya gönderin
$order->order->amount;             // geçidin hesapladığı toplam
```

Ödendiğinde müşteri `successUrl` adresinize 3D dönüşüyle aynı alanlarla POST edilir; kesin sonucu `retrieveOrder()` verir; `order.paid` webhook'u geldiğinde de onu çağırın. `$order->transaction->paymentStatus` sonradan yapılan iadeyi gösterir.

```php
use Gurmehub\Odemehub\Request\{RetrieveOrder, UpdateOrder};

$order = $client->retrieveOrder(new RetrieveOrder($token));
$order->order->isPaid();
$order->order->transaction?->token;
$order->order->customer?->reference;   // listelerde de her siparişte gelir

// Açık siparişte yalnızca gönderilen alanlar değişir; kalemler gönderilirse tamamı yenilenir.
$client->updateOrder(new UpdateOrder(token: $token, description: 'Hediye paketi', clear: ['cancel_url']));
```

`create-*` çağrıları aynı kanal ve referans için tekrarlanabilir: aynı referansla ikinci kez açılan sipariş, link ya da (henüz ödenmemiş) abonelik yeni gönderilenlerle güncellenir ve kendi token'ıyla döner. Ödenmiş sipariş değişmez. **Bekleyen ödeme varken güncellenemez:** ödeme sayfasında son 15 dakika içinde başlamış bir ödeme varsa `create-*` ve `update-*` çağrıları `channel_reference`/`token` alanında "bekleyen bir ödeme var, tamamlanmasını bekleyin" ile reddedilir.

## Ödeme linki

Link kim açarsa onun ödeyebileceği bir sayfadır; kapatılana ya da son gününe kadar tekrar tekrar ödenir. Müşterisi yoktur.

```php
use Gurmehub\Odemehub\Enum\Currency;
use Gurmehub\Odemehub\Request\{CreatePaymentLink, RetrievePaymentLink, UpdatePaymentLink};

$link = $client->createPaymentLink(new CreatePaymentLink(
    items: [new Item(name: 'Bağış', unitAmount: '100.00', quantity: 1, taxRate: '0')],
    currency: Currency::TRY,
    channelReference: 'LNK-1',          // boş bırakılırsa geçit LINK{n} üretir
    expiresAt: '2026-12-31',            // çalışma alanının saat dilimine göre gün
));

$link->paymentLink->checkoutUrl;        // linkin kendisi; ödenemezken (kapalı, süresi geçmiş) null
$link->paymentLink->expiresAt;          // verilen günün sonu (çalışma alanı saatiyle), ISO 8601 UTC
$link->paymentLink->isTest;             // ödemeleri şu an test ortamında mı alınıyor

$detail = $client->retrievePaymentLink(new RetrievePaymentLink($link->paymentLink->token));
$detail->transactions;                  // son 50 deneme, yeniden eskiye
$detail->transactionsCount;             // linkteki denemelerin tamamının sayısı
$detail->successful();                  // listelenenlerden başarılı olanlar

$client->updatePaymentLink(new UpdatePaymentLink(token: $link->paymentLink->token, isActive: false));
```

Kanal verilmezse link istemcinin kanalına açılır. Panelin açtığı linklere ulaşmak için kanal olarak `ChannelMessage::ODEMEHUB_CHANNEL` verin.

## Abonelik

İlk yenileme ödeme sayfasında ödenir ve kart orada saklanır; sonrakiler o karttan çekilir. Müşteri referansı zorunludur. Hesap kart saklamalı ve 3D ödeme almalıdır.

```php
use Gurmehub\Odemehub\Enum\{Period, SubscriptionStatus};
use Gurmehub\Odemehub\Request\{CreateSubscription, RetrieveSubscription, UpdateSubscription};

$subscription = $client->createSubscription(new CreateSubscription(
    channelReference: 'ABO-1',
    period: Period::Monthly,
    successUrl: 'https://magazam.com/abonelik/donus',
    items: [new Item(name: 'Premium', unitAmount: '99.90', quantity: 1, taxRate: '20')],
    customer: $customer,
    renewalLimit: 12,                   // boş: iptale kadar
));

$subscription->subscription->checkoutUrl;

$current = $client->retrieveSubscription(new RetrieveSubscription($token));
$current->subscription->status;          // Enum\SubscriptionStatus: Pending, Active, PastDue, Cancelled, Completed
$current->subscription->renewal->paidAt; // içinde bulunulan yenileme
$current->subscription->nextPaymentAt;
$current->customer->reference;           // listelerde $subscription->customer olarak gelir

// Dönem, kalemler, ödeme sayısı değişir; iptal de buradan:
$client->updateSubscription(new UpdateSubscription(token: $token, status: SubscriptionStatus::Cancelled));
```

İptalde para iade edilmez; ödenmiş dönem sonuna kadar sürer, sonra abonelik biter (`subscription.ended`). Ödenmiş dönem yoksa hemen `cancelled` olur.

İlk ödemeden sonra kanal, ödeme hesabı, para birimi, dönem ve müşteri referansı değiştirilemez; geçit bunları 422 ile reddeder (aynı değeri yeniden göndermek değişiklik sayılmaz).

## Kayıtlı kartlar

Kart ödeme sırasında (`shouldSave: true`) ya da ödemesiz saklanır. Kanal ve müşteri referansı ikilisinin altında durur; kart yanıtlarındaki `customer` yalnızca `reference` taşır.

```php
use Gurmehub\Odemehub\Request\{CreateSavedCard, DeleteSavedCard, RetrieveSavedCardsByReference, UpdateSavedCard};

$saved = $client->createSavedCard(new CreateSavedCard(customer: $customer, card: $card));
$saved->savedCard?->token;              // sağlayıcı saklamadıysa null, nedeni result->message

$cards = $client->retrieveSavedCardsByReference(new RetrieveSavedCardsByReference(
    customerReference: 'musteri-88',
));
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

## Referansla ve tarihle listeleme

Her kaynak kendi referansıyla ya da bir tarih aralığıyla bulunur. Aralık en çok 7 gündür ve çalışma alanının saat dilimindedir; boş bırakılırsa son 7 gün.

```php
use Gurmehub\Odemehub\Request\{RetrievePaymentByReference, RetrievePaymentsByChannelReference};

// Yanıtı alınamayan bir ödemenin akıbeti: referanstaki son ödeme
$client->retrievePaymentByReference(new RetrievePaymentByReference('SIP-10231'));

// Kanaldaki bütün denemeler, reddedilenler dahil, durumu ve tutarıyla
$list = $client->retrievePaymentsByChannelReference(new RetrievePaymentsByChannelReference('2026-09-26', '2026-10-02'));
foreach ($list->payments as $transaction) {
    $transaction->status;               // Enum\TransactionStatus; Timeout: sağlayıcı yanıt vermedi
    $transaction->paymentStatus;        // Enum\PaymentStatus: Paid, Refunded, PartiallyRefunded...
}
```

Aynısı `retrieveOrderByReference` / `retrieveOrdersByChannelReference`, `retrieveSubscriptionByReference` / `retrieveSubscriptionsByChannelReference`, `retrievePaymentLinkByReference` / `retrievePaymentLinksByChannelReference` için de geçerlidir.

## Webhook

Sipariş ödendiğinde, link ödemesi alındığında, abonelik durum değiştirdiğinde, API ödemesi bittiğinde ve bir ödeme iade ya da iptal edildiğinde geçit imzalı JSON POST eder. Adresler kodda verilmez; panelde **Ayarlar → Webhook** sayfasında kanal, olay ve adres seçilerek tanımlanır.

Olaylar (`Enum\WebhookEvent`):

| Kaynak | Olaylar |
| --- | --- |
| Sipariş | `order.paid`, `order.payment_refunded`, `order.payment_cancelled` |
| Ödeme linki | `payment_link.paid`, `payment_link.payment_refunded`, `payment_link.payment_cancelled` |
| Abonelik | `subscription.active`, `subscription.past_due`, `subscription.cancelled`, `subscription.ended`, `subscription.completed`, `subscription.payment_refunded`, `subscription.payment_cancelled` |
| API ödemesi | `transaction.successful`, `transaction.failed`, `transaction.expired`, `transaction.payment_refunded`, `transaction.payment_cancelled` |

Sipariş, link ya da abonelikte alınan ödeme için `transaction.*` gelmez; o kaynağın kendi olayı gelir.

**Webhook nihai sonuç değildir.** Gövde yalnızca kaynağın token'ını (para hareketi varsa yanında ödemenin token'ını) taşır. Kararı, token ile geçide sorduğunuz yanıta göre verin ve yanıtı kendi kaydınızla (referans, tutar, durum) karşılaştırın:

```php
use Gurmehub\Odemehub\Exception\SignatureException;
use Gurmehub\Odemehub\Request\{RetrieveOrder, RetrievePayment, RetrieveSubscription};

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
    $order = $client->retrieveOrder(new RetrieveOrder($webhook->orderToken))->order;
    $order->status;                         // Enum\OrderStatus::Paid
    $order->transaction?->paymentStatus;    // Enum\PaymentStatus: Refunded, PartiallyRefunded ...
} elseif ($webhook->subscriptionToken !== null) {
    $subscription = $client->retrieveSubscription(new RetrieveSubscription($webhook->subscriptionToken))->subscription;
} elseif ($webhook->transactionToken !== null) {   // transaction.* ve payment_link.*
    $transaction = $client->retrievePayment(new RetrievePayment($webhook->transactionToken))->transaction;
    $transaction->paymentLinkToken;         // linkte alınan ödemede linkin token'ı
}

http_response_code(204);
```

Abonelik ve link ödemelerinin iade/iptal olaylarında `transactionToken` da gelir; `retrievePayment()` yanıtındaki `orderToken` / `paymentLinkToken` / `subscriptionToken` ödemenin gerçekten o kaynağa ait olduğunu gösterir.

Yalnızca doğrulamak için `$client->verifyWebhook(...)` `bool` döner. Adres üretimde https ve herkese açık olmalıdır; geçit 2xx yanıt alana kadar 60 sn, 5 dk, 15 dk ve 30 dk arayla toplam 5 kez dener. Yönlendirmeleri izlemez.

## Hatalar

Hepsi `Gurmehub\Odemehub\Exception\OdemehubException` türündendir.

| İstisna | Durum | Anlamı |
| --- | --- | --- |
| `AuthenticationException` | 401 | API anahtarı yanlış, imza tutmuyor ya da zaman damgası aralık dışında |
| `ForbiddenException` | 403 | Çalışma alanı işlem yapamıyor (ödenmemiş bakiye, plan) ya da plan bu özelliği kapsamıyor |
| `NotFoundException` | 404 | Token ya da kanal + referansla istenen kayıt (ödeme, sipariş, link, abonelik, kayıtlı kart) yok |
| `ValidationException` | 422 | Alan hataları; `$e->errors` noktalı alan adıyla (`transaction.amount`, `order.items.0.name`) |
| `RateLimitException` | 429 | İstek sınırı; `$e->retryAfter` saniye |
| `SignatureException` | — | Yanıtın ya da webhook'un imzası doğrulanamadı; içeriğe güvenmeyin |
| `TransportException` | — | Geçide ulaşılamadı; ödemenin akıbetini `retrievePaymentByReference` ile sorun |
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

## 2.0.0'daki kırıcı değişiklikler

1.x'ten geçerken dikkat edilecekler:

- **Uç noktalar yeniden adlandırıldı.** Her metot uç noktanın adını taşır: `orderPayment()` → `createOrder()`, `subscriptionPayment()` → `createSubscription()`, `saveCard()` → `createSavedCard()`, `savedCards()` → `retrieveSavedCardsByReference()`, `defaultSavedCard()` → `updateSavedCard()`, `cancelSubscription()` → `updateSubscription(status: SubscriptionStatus::Cancelled)`, `retrieveTransactions()` → `retrievePaymentsByChannelReference()`. `saveProduct()` kalktı; kalemler isteğin içinde gönderilir.
- **Tekil kaydı adlandıran istek alanı her yerde `token`.** `RefundPayment` ve `CancelPayment` artık `transactionToken` değil `token` alır.
- **Yanıtlar API JSON'unu birebir yansıtır.** Ödeme yanıtında düz `transactionToken` / `channelToken` / `channelReference` yerine `$payment->transaction->token` vb.; `transaction` artık `status`, `paymentStatus`, `securityType`, `amount`, `baseAmount`, `currency`, `installmentNumber`, `isTest`, `createdAt` de taşır. Müşteri `$payment->customer` altındadır.
- **Sabit kümeli alanlar tipli.** Yanıtlarda `currency`, `period`, `status` (sipariş, abonelik, işlem), `paymentStatus`, `securityType`, `refund->type`, kart `scheme` ve `type` artık dize değil `Gurmehub\Odemehub\Enum\*` değerleridir (`?Currency`, `?OrderStatus`, `?SubscriptionStatus`, `?TransactionStatus`, `?PaymentStatus`, `?SecurityType`, `?RefundType`, `?CardScheme`, `?CardType`, `?Period`). Karşılaştırmayı enum'la yapın (`$order->status === OrderStatus::Paid`), metin gerekiyorsa `->value` kullanın. Geçit SDK'nın bilmediği yeni bir değer gönderirse alan çökmeden `null` olur.
- **Müşteri:** `OrderDetails` / `SubscriptionDetails` müşteriyi hem üstte (`->customer`) hem varlığın üzerinde (`->order->customer`, `->subscription->customer`) taşır; listelerde her öğenin `customer` alanındadır.
- **Kayıtlı kartlar** kanal + müşteri referansıyla tutulur: `RetrieveSavedCardsByReference` `email` almaz, kart yanıtlarındaki `customer` yalnızca `reference` taşır. `deleteSavedCard()` `delete-saved-card/{token}` adresine gider.
- **Ödeme linki:** `checkoutUrl` link ödenemezken `null`; `expiresAt` ISO 8601 UTC; `retrievePaymentLink()` son 50 denemeyi ve `transactionsCount`'u döner.
- **Hatalar:** bulunamayan kayıt artık 422 değil 404'tür ve `NotFoundException` atılır. Yeni istisnalar: `ForbiddenException` (403), `NotFoundException` (404), `RateLimitException` (429, `retryAfter`).
- **Webhook:** `orderWebhook()`, `subscriptionWebhook()`, `transactionWebhook()` yerine tek `webhook()` (ve `verifyWebhook()`); imza yöntem, yol, gövde ve zaman damgası üzerindendir. Gövde artık yalnızca token taşır (`orderToken`, `paymentLinkToken`, `subscriptionToken`, `transactionToken`); durum `retrieve*()` ile sorulur. Adresler panelde tanımlandığı için `SecurePayment`, `CreateOrder`, `UpdateOrder`, `CreateSubscription`, `UpdateSubscription` artık `webhookUrl` almaz.

## Örnekler

`example/` klasöründe her uç nokta grubu için çalışan bir sayfa vardır. `php -S localhost:8080 -t example` ile sunun; kimlik bilgileri `example/config.php` içindedir ve ortam değişkenleriyle değiştirilir.
