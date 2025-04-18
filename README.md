# Trendyol Marketplace API Laravel Paketi

Bu paket, Trendyol Marketplace API'si ile entegrasyon sağlamak için Laravel uygulamalarında kullanılabilecek bir istemci sağlar.

## Kurulum

### Packagist Üzerinden Kurulum (Tavsiye Edilen)

Paketi Composer aracılığıyla yükleyin:

```bash
composer require wiensa/trendyol-sp-api
```

### GitHub Üzerinden Kurulum

Eğer paket henüz Packagist'e yüklenmemişse, GitHub üzerinden doğrudan kurulum yapabilirsiniz. Bu yöntem için `composer.json` dosyanıza aşağıdaki kod bloğunu ekleyin:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/wiensa/trendyol-sp-api"
    }
],
"require": {
    "wiensa/trendyol-sp-api": "dev-main"
}
```

Sonra Composer'ı güncellemeyi unutmayın:

```bash
composer update
```

### Lokal Geliştirme İçin Kurulum

Geliştirme sürecinde lokal olarak kullanmak için `composer.json` dosyanıza path repository ekleyebilirsiniz:

```json
"repositories": [
    {
        "type": "path",
        "url": "../trendyol-sp-api"
    }
],
"require": {
    "wiensa/trendyol-sp-api": "*"
}
```

Laravel 5.5 ve üstü için, paket otomatik olarak kaydedilecektir.

Yapılandırma dosyasını yayınlamak için:

```bash
php artisan vendor:publish --provider="TrendyolApi\TrendyolSpApi\Providers\TrendyolServiceProvider" --tag="config"
```

## Yapılandırma

Paket yapılandırması, yayınlanan `config/trendyol.php` dosyasında veya `.env` dosyasında aşağıdaki şekilde ayarlanabilir:

```dotenv
TRENDYOL_SUPPLIER_ID=your-supplier-id
TRENDYOL_API_KEY=your-api-key
TRENDYOL_API_SECRET=your-api-secret
TRENDYOL_API_URL=https://api.trendyol.com/sapigw
TRENDYOL_CACHE_ENABLED=true
TRENDYOL_CACHE_TTL=3600
TRENDYOL_DEBUG=false
```

## Kullanım

### Facade Kullanımı

```php
use TrendyolApi\TrendyolSpApi\Facades\Trendyol;

// Ürünleri listeleme
$products = Trendyol::products()->list(['page' => 0, 'size' => 50]);

// Sipariş bilgilerini alma
$orders = Trendyol::orders()->list(['status' => 'Created', 'startDate' => '2023-01-01', 'endDate' => '2023-12-31']);

// Kategorileri listeleme
$categories = Trendyol::categories()->list();

// Markaları arama
$brands = Trendyol::brands()->search('Apple');

// İadeleri listeleme
$returns = Trendyol::returns()->list(['startDate' => '2023-01-01', 'endDate' => '2023-12-31']);

// Müşteri sorularını listeleme
$questions = Trendyol::customerQuestions()->list(['status' => 'WAITING_FOR_ANSWER']);

// Şikayetleri/talepleri listeleme
$claims = Trendyol::claims()->list(['status' => 'OPEN']);

// Kargo firmalarını listeleme
$shipmentProviders = Trendyol::shipmentProviders()->list();
```

### Helper Fonksiyonu Kullanımı

```php
// Ürünleri listeleme
$products = trendyol()->products()->list(['page' => 0, 'size' => 50]);

// Sipariş bilgilerini alma
$orders = trendyol()->orders()->list();

// İadeleri listeleme
$returns = trendyol()->returns()->list();

// Müşteri sorusuna yanıt verme
$answer = trendyol()->customerQuestions()->answer(12345, 'Ürünümüz 2 yıl garantilidir.');
```

### Dependency Injection Kullanımı

```php
use TrendyolApi\TrendyolSpApi\Trendyol;

class ProductController extends Controller
{
    protected Trendyol $trendyol;
    
    public function __construct(Trendyol $trendyol)
    {
        $this->trendyol = $trendyol;
    }
    
    public function index()
    {
        return $this->trendyol->products()->list();
    }
}
```

## Özellikler

- **Ürün Yönetimi**: 
  - Ürün listeleme, detay görüntüleme
  - Ürün oluşturma (tekli ve toplu)
  - Ürün güncelleme (tekli ve toplu)
  - Ürün silme
  - Stok ve fiyat güncelleme

- **Sipariş Yönetimi**: 
  - Siparişleri listeleme ve detay görüntüleme
  - Sipariş paketini onaylama veya iptal etme
  - Kargo takip numarası güncelleme
  - Fatura bilgisi/dosyası gönderme

- **İade Yönetimi**: 
  - İadeleri listeleme ve detay görüntüleme
  - İade durumunu güncelleme (onaylama/reddetme)
  - İade kargo takip numarasını güncelleme

- **Müşteri Soruları Yönetimi**: 
  - Soruları listeleme ve detay görüntüleme
  - Soruları yanıtlama
  - Soruları eskalasyon yapma/üst kademeye iletme

- **Talep/Şikayet Yönetimi**: 
  - Talepleri listeleme ve detay görüntüleme
  - Talep durumunu güncelleme
  - Talebe not ekleme
  - Talebe döküman/delil yükleme

- **Kargo ve Sevkiyat Yönetimi**: 
  - Kargo firmalarını listeleme
  - Tedarikçi kargo hesaplarını görüntüleme
  - Sevkiyat çıkışlarını listeleme ve detay görüntüleme
  - Kargo etiketi indirme (tekli ve toplu)
  - Sevkiyat çıkışı oluşturma
  - Teslimat seçeneklerini görüntüleme

- **Kategori ve Marka Yönetimi**: 
  - Kategori ve markaları listeleme ve arama

- **Tedarikçi Adresi Yönetimi**: 
  - Tedarikçi adreslerini yönetme

- **Genel Özellikler**:
  - API İstek Rate Limiting: API istek sınırlamalarına otomatik uyum sağlama
  - Önbellek Desteği: API yanıtlarını önbelleğe alma özelliği
  - Debug Modu: API isteklerini ve yanıtlarını günlüğe kaydetme özelliği

## Hata Yönetimi

```php
use TrendyolApi\TrendyolSpApi\Exceptions\TrendyolApiException;

try {
    $products = Trendyol::products()->list();
} catch (TrendyolApiException $e) {
    // API hatası işlemleri
    $error_code = $e->getCode();
    $error_message = $e->getMessage();
} catch (\Exception $e) {
    // Diğer hatalar
}
```

## Detaylı Kullanım Örnekleri

### İade İşlemleri

```php
// İadeleri listeleme
$returns = Trendyol::returns()->list([
    'startDate' => '2023-01-01',
    'endDate' => '2023-12-31',
    'status' => 'PENDING'
]);

// İade detayını görüntüleme
$returnDetail = Trendyol::returns()->get(12345);

// İade durumunu güncelleme (onaylama)
$updateStatus = Trendyol::returns()->updateStatus(12345, 'APPROVED');

// İade durumunu güncelleme (reddetme)
$updateStatus = Trendyol::returns()->updateStatus(12345, 'REJECTED', 'Ürün hasarlı geldi');

// İade kargo takip numarasını güncelleme
$updateTracking = Trendyol::returns()->updateTrackingNumber(12345, '1234567890');
```

### Müşteri Soruları

```php
// Müşteri sorularını listeleme
$questions = Trendyol::customerQuestions()->list([
    'status' => 'WAITING_FOR_ANSWER',
    'startDate' => '2023-01-01',
    'endDate' => '2023-12-31'
]);

// Soru detayını görüntüleme
$questionDetail = Trendyol::customerQuestions()->get(12345);

// Soruyu yanıtlama
$answer = Trendyol::customerQuestions()->answer(12345, 'Ürünümüz 2 yıl garantilidir.');

// Soruyu eskalasyon yapma/üst kademeye iletme
$escalate = Trendyol::customerQuestions()->escalate(12345, 'Teknik bilgi gerektiren bir soru');
```

### Talep/Şikayet İşlemleri

```php
// Talepleri listeleme
$claims = Trendyol::claims()->list([
    'status' => 'OPEN',
    'startDate' => '2023-01-01',
    'endDate' => '2023-12-31'
]);

// Talep detayını görüntüleme
$claimDetail = Trendyol::claims()->get(12345);

// Talebe not ekleme
$addNote = Trendyol::claims()->addNote(12345, 'Müşteri ile görüşüldü, çözüm sağlandı.');

// Talep durumunu güncelleme
$updateStatus = Trendyol::claims()->updateStatus(12345, 'SOLVED', 'Müşteriye yeni ürün gönderildi');

// Talebe döküman/delil yükleme (Base64 formatında dosya içeriği)
$uploadDocument = Trendyol::claims()->uploadDocument(
    12345,
    base64_encode(file_get_contents('kargo_fisi.pdf')),
    'kargo_fisi.pdf'
);
```

### Kargo ve Sevkiyat İşlemleri

```php
// Kargo firmalarını listeleme
$shipmentProviders = Trendyol::shipmentProviders()->list();

// Tedarikçi kargo hesaplarını görüntüleme
$supplierAccounts = Trendyol::shipmentProviders()->getSupplierAccounts();

// Sevkiyat çıkışlarını listeleme
$shipmentOutbounds = Trendyol::shipmentProviders()->getShipmentOutbounds([
    'startDate' => '2023-01-01',
    'endDate' => '2023-12-31'
]);

// Sevkiyat çıkışı detayını görüntüleme
$shipmentOutbound = Trendyol::shipmentProviders()->getShipmentOutbound(12345);

// Teslimat seçeneklerini görüntüleme
$deliveryOptions = Trendyol::shipmentProviders()->getDeliveryOptions();

// Kargo etiketi indirme
$shippingLabel = Trendyol::shipmentProviders()->downloadShippingLabel(12345);

// Toplu kargo etiketi indirme
$bulkShippingLabels = Trendyol::shipmentProviders()->downloadBulkShippingLabel([12345, 67890]);

// Sevkiyat çıkışı oluşturma
$createOutbound = Trendyol::shipmentProviders()->createShipmentOutbound([
    'shipmentProvider' => 'Aras',
    'packageDetails' => [
        // ...
    ]
]);
```

## Testler

Testleri çalıştırmak için:

```bash
composer test
```

## Lisans

Bu paket MIT lisansı altında lisanslanmıştır. 