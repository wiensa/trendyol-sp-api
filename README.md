# Trendyol Marketplace API Laravel Paketi

Bu paket, Trendyol Marketplace API'si ile entegrasyon sağlamak için Laravel uygulamalarında kullanılabilecek bir istemci sağlar.

## Kurulum

Paketi Composer aracılığıyla yükleyin:

```bash
composer require serkan/trendyol-sp-api
```

Laravel 5.5 ve üstü için, paket otomatik olarak kaydedilecektir.

Yapılandırma dosyasını yayınlamak için:

```bash
php artisan vendor:publish --provider="Serkan\TrendyolSpApi\Providers\TrendyolServiceProvider" --tag="config"
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
use Serkan\TrendyolSpApi\Facades\Trendyol;

// Ürünleri listeleme
$products = Trendyol::products()->list(['page' => 0, 'size' => 50]);

// Sipariş bilgilerini alma
$orders = Trendyol::orders()->list(['status' => 'Created', 'startDate' => '2023-01-01', 'endDate' => '2023-12-31']);

// Kategorileri listeleme
$categories = Trendyol::categories()->list();

// Markaları arama
$brands = Trendyol::brands()->search('Apple');
```

### Helper Fonksiyonu Kullanımı

```php
// Ürünleri listeleme
$products = trendyol()->products()->list(['page' => 0, 'size' => 50]);

// Sipariş bilgilerini alma
$orders = trendyol()->orders()->list();
```

### Dependency Injection Kullanımı

```php
use Serkan\TrendyolSpApi\Trendyol;

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

- **Ürün Yönetimi**: Ürün listeleme, oluşturma, güncelleme ve silme işlemleri
- **Sipariş Yönetimi**: Siparişleri listeleme, onaylama, iptal etme, fatura gönderme
- **Kategori ve Marka Yönetimi**: Kategori ve markaları listeleme ve arama
- **Tedarikçi Adresi Yönetimi**: Tedarikçi adreslerini yönetme
- **API İstek Rate Limiting**: API istek sınırlamalarına otomatik uyum sağlama
- **Önbellek Desteği**: API yanıtlarını önbelleğe alma özelliği
- **Debug Modu**: API isteklerini ve yanıtlarını günlüğe kaydetme özelliği

## Hata Yönetimi

```php
use Serkan\TrendyolSpApi\Exceptions\TrendyolApiException;

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

## Testler

Testleri çalıştırmak için:

```bash
composer test
```

## Lisans

Bu paket MIT lisansı altında lisanslanmıştır. 