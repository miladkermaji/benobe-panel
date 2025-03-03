<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentGatewaySeeder extends Seeder
{
 public function run()
 {
  $defaultLogo = 'https://cdn-icons-png.flaticon.com/512/888/888879.png';

  $gateways = [
   [
    'name' => 'local',
    'title' => 'درگاه پرداخت تست',
    'logo' => $defaultLogo,
    'is_active' => false,
    'settings' => json_encode([
     'callbackUrl' => '/callback',
    ]),
   ],
   [
    'name' => 'gooyapay',
    'title' => 'گويا پی',
    'logo' => 'https://gooyapay.ir/wp-content/uploads/2023/06/logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('GOOYAPAY_MERCHANT_ID', ''),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'fanavacard',
    'title' => 'فن آوا کارت',
    'logo' => 'https://fanavacard.ir/wp-content/uploads/2022/11/cropped-Fanava-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'username' => env('FANAVACARD_USERNAME', ''),
     'password' => env('FANAVACARD_PASSWORD', ''),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'atipay',
    'title' => 'آتی پی',
    'logo' => 'https://atipay.net/wp-content/uploads/2022/08/atipay-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'apikey' => env('ATIPAY_APIKEY', ''),
     'currency' => 'R',
    ]),
   ],
   [
    'name' => 'asanpardakht',
    'title' => 'آسان پرداخت',
    'logo' => 'https://asanpardakht.ir/assets/images/aplogo.png',
    'is_active' => false,
    'settings' => json_encode([
     'username' => env('ASANPARDAKHT_USERNAME', ''),
     'password' => env('ASANPARDAKHT_PASSWORD', ''),
     'merchantConfigID' => env('ASANPARDAKHT_MERCHANT_CONFIG_ID', ''),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'behpardakht',
    'title' => 'به پرداخت بانک ملت',
    'logo' => 'https://behpardakht.com/wp-content/uploads/2023/03/behpardakht-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'terminalId' => env('BEHPARDAKHT_TERMINAL_ID', ''),
     'username' => env('BEHPARDAKHT_USERNAME', ''),
     'password' => env('BEHPARDAKHT_PASSWORD', ''),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'digipay',
    'title' => 'دیجی پی',
    'logo' => 'https://mydigipay.com/assets/images/digipay-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'username' => env('DIGIPAY_USERNAME', ''),
     'password' => env('DIGIPAY_PASSWORD', ''),
     'client_id' => env('DIGIPAY_CLIENT_ID', ''),
     'client_secret' => env('DIGIPAY_CLIENT_SECRET', ''),
     'currency' => 'R',
    ]),
   ],
   [
    'name' => 'etebarino',
    'title' => 'اعتبارینو',
    'logo' => 'https://etebarino.com/wp-content/uploads/2020/05/etebarino-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('ETEBARINO_MERCHANT_ID', ''),
     'terminalId' => env('ETEBARINO_TERMINAL_ID', ''),
     'username' => env('ETEBARINO_USERNAME', ''),
     'password' => env('ETEBARINO_PASSWORD', ''),
    ]),
   ],
   [
    'name' => 'idpay',
    'title' => 'آی دی پی (idPay)',
    'logo' => 'https://idpay.ir/assets/images/idpay-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('IDPAY_MERCHANT_ID', ''),
     'sandbox' => env('IDPAY_SANDBOX', true),
     'currency' => 'R',
    ]),
   ],
   [
    'name' => 'irandargah',
    'title' => 'ایران درگاه',
    'logo' => 'https://irandargah.com/themes/iranDargah/assets/img/logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('IRANDARGAH_MERCHANT_ID', ''),
     'sandbox' => env('IRANDARGAH_SANDBOX', false),
     'currency' => 'R',
    ]),
   ],
   [
    'name' => 'irankish',
    'title' => 'ایران کیش',
    'logo' => 'https://www.irankish.com/Content/images/irankish-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'terminalId' => env('IRANKISH_TERMINAL_ID', ''),
     'password' => env('IRANKISH_PASSWORD', ''),
     'acceptorId' => env('IRANKISH_ACCEPTOR_ID', ''),
     'pubKey' => env('IRANKISH_PUB_KEY', ''),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'jibit',
    'title' => 'جیبیت',
    'logo' => 'https://jibit.ir/assets/images/jibit-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'apiKey' => env('JIBIT_API_KEY', ''),
     'apiSecret' => env('JIBIT_API_SECRET', ''),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'nextpay',
    'title' => 'نکست پی',
    'logo' => 'https://nextpay.ir/wp-content/uploads/2022/03/nextpay-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('NEXTPAY_MERCHANT_ID', ''),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'omidpay',
    'title' => 'امید پی',
    'logo' => 'https://omidpay.ir/wp-content/uploads/2022/05/omidpay-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'username' => env('OMIDPAY_USERNAME', ''),
     'merchantId' => env('OMIDPAY_MERCHANT_ID', ''),
     'password' => env('OMIDPAY_PASSWORD', ''),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'parsian',
    'title' => 'بانک پارسیان',
    'logo' => 'https://www.parsian-bank.ir/portal/statics/default/images/logo-fa.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('PARSIAN_MERCHANT_ID', ''),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'parspal',
    'title' => 'پارس پال',
    'logo' => 'https://parspal.com/wp-content/uploads/2020/03/parspal-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('PARSPAL_MERCHANT_ID', ''),
     'sandbox' => env('PARSPAL_SANDBOX', false),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'pasargad',
    'title' => 'بانک پاسارگاد',
    'logo' => 'https://www.bpi.ir/Content/images/pasargad-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('PASARGAD_MERCHANT_ID', ''),
     'terminalCode' => env('PASARGAD_TERMINAL_CODE', ''),
     'certificate' => env('PASARGAD_CERTIFICATE', ''),
     'certificateType' => 'xml_file',
     'currency' => 'R',
    ]),
   ],
   [
    'name' => 'payir',
    'title' => 'پی (pay.ir)',
    'logo' => 'https://pay.ir/assets/img/pay-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('PAYIR_MERCHANT_ID', 'test'),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'paypal',
    'title' => 'پی‌پال',
    'logo' => 'https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-200px.png',
    'is_active' => false,
    'settings' => json_encode([
     'id' => env('PAYPAL_ID', ''),
     'mode' => env('PAYPAL_MODE', 'normal'),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'payping',
    'title' => 'پی پینگ',
    'logo' => 'https://payping.ir/assets/images/payping-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('PAYPING_MERCHANT_ID', ''),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'paystar',
    'title' => 'پی استار',
    'logo' => 'https://paystar.ir/wp-content/uploads/2020/10/paystar-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'gatewayId' => env('PAYSTAR_GATEWAY_ID', ''),
     'signKey' => env('PAYSTAR_SIGN_KEY', ''),
     'currency' => 'R',
    ]),
   ],
   [
    'name' => 'poolam',
    'title' => 'پولام',
    'logo' => 'https://poolam.ir/wp-content/uploads/2020/06/poolam-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('POOLAM_MERCHANT_ID', ''),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'pna',
    'title' => 'پرداخت نوین آرین',
    'logo' => 'https://pna.shaparak.ir/Content/images/pna-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'CorporationPin' => env('PNA_CORPORATION_PIN', ''),
     'currency' => 'R',
    ]),
   ],
   [
    'name' => 'sadad',
    'title' => 'سداد',
    'logo' => 'https://sadadpsp.ir/wp-content/uploads/2023/05/sadad-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'key' => env('SADAD_KEY', ''),
     'merchantId' => env('SADAD_MERCHANT_ID', ''),
     'terminalId' => env('SADAD_TERMINAL_ID', ''),
     'mode' => env('SADAD_MODE', 'normal'),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'saman',
    'title' => 'بانک سامان',
    'logo' => 'https://www.sb24.ir/Content/Images/saman-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('SAMAN_MERCHANT_ID', ''),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'sep',
    'title' => 'پرداخت الکترونیک سامان',
    'logo' => 'https://sep.ir/assets/images/sep-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'terminalId' => env('SEP_TERMINAL_ID', ''),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'sepehr',
    'title' => 'سپهر صادرات',
    'logo' => 'https://www.bsi.ir/Content/Images/sepehr-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'terminalId' => env('SEPEHR_TERMINAL_ID', ''),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'walleta',
    'title' => 'والتا',
    'logo' => 'https://walleta.ir/wp-content/uploads/2020/04/walleta-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('WALLETA_MERCHANT_ID', ''),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'yekpay',
    'title' => 'یک پی',
    'logo' => 'https://yekpay.com/assets/images/yekpay-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('YEKPAY_MERCHANT_ID', ''),
     'fromCurrencyCode' => 978,
     'toCurrencyCode' => 364,
    ]),
   ],
   [
    'name' => 'zarinpal',
    'title' => 'زرین پال',
    'logo' => 'https://zarinpal.com/img/merchant/pattern-zarinpal.png',
    'is_active' => true,
    'settings' => json_encode([
     'merchantId' => env('ZARINPAL_MERCHANT_ID', ''),
     'sandbox' => env('ZARINPAL_SANDBOX', true),
     'mode' => env('ZARINPAL_MODE', 'sandbox'),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'zibal',
    'title' => 'زیبال',
    'logo' => 'https://zibal.ir/assets/images/zibal-ir.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('ZIBAL_MERCHANT_ID', ''),
     'mode' => env('ZIBAL_MODE', 'normal'),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'sepordeh',
    'title' => 'سپرده',
    'logo' => 'https://sepordeh.com/assets/app/images/logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('SEPORDEH_MERCHANT_ID', ''),
     'mode' => env('SEPORDEH_MODE', 'normal'),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'rayanpay',
    'title' => 'رایان پی',
    'logo' => 'https://rayanpay.com/wp-content/uploads/2020/05/rayanpay-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'username' => env('RAYANPAY_USERNAME', ''),
     'client_id' => env('RAYANPAY_CLIENT_ID', ''),
     'password' => env('RAYANPAY_PASSWORD', ''),
     'currency' => 'R',
    ]),
   ],
   [
    'name' => 'shepa',
    'title' => 'شپا',
    'logo' => 'https://shepa.com/wp-content/uploads/2020/03/shepa-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('SHEPA_MERCHANT_ID', ''),
     'sandbox' => env('SHEPA_SANDBOX', false),
     'currency' => 'R',
    ]),
   ],
   [
    'name' => 'sizpay',
    'title' => 'سیز پی',
    'logo' => 'https://sizpay.ir/wp-content/uploads/2020/09/sizpay-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('SIZPAY_MERCHANT_ID', ''),
     'terminal' => env('SIZPAY_TERMINAL', ''),
     'username' => env('SIZPAY_USERNAME', ''),
     'password' => env('SIZPAY_PASSWORD', ''),
     'SignData' => env('SIZPAY_SIGNDATA', ''),
     'currency' => 'R',
    ]),
   ],
   [
    'name' => 'vandar',
    'title' => 'وندار',
    'logo' => 'https://vandar.io/assets/images/vandar-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('VANDAR_MERCHANT_ID', ''),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'aqayepardakht',
    'title' => 'آقای پرداخت',
    'logo' => 'https://panel.aqayepardakht.ir/assets/images/aqayepardakht-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'pin' => env('AQAYEPARDAKHT_PIN', ''),
     'mode' => env('AQAYEPARDAKHT_MODE', 'normal'),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'azki',
    'title' => 'ازکی',
    'logo' => 'https://cdn.azkivam.com/static/media/azki-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('AZKI_MERCHANT_ID', ''),
     'key' => env('AZKI_KEY', ''),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'payfa',
    'title' => 'پی فا',
    'logo' => 'https://payfa.com/v2/assets/images/payfa-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'apiKey' => env('PAYFA_API_KEY', ''),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'toman',
    'title' => 'تومان',
    'logo' => 'https://toman.ir/wp-content/uploads/2020/06/toman-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'shop_slug' => env('TOMAN_SHOP_SLUG', ''),
     'auth_code' => env('TOMAN_AUTH_CODE', ''),
    ]),
   ],
   [
    'name' => 'bitpay',
    'title' => 'بیت پی',
    'logo' => 'https://bitpay.ir/wp-content/uploads/2020/01/bitpay-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'api_token' => env('BITPAY_API_TOKEN', ''),
     'currency' => 'R',
    ]),
   ],
   [
    'name' => 'minipay',
    'title' => 'مینی پی',
    'logo' => 'https://minipay.me/wp-content/uploads/2020/04/minipay-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'merchantId' => env('MINIPAY_MERCHANT_ID', ''),
     'currency' => 'T',
    ]),
   ],
   [
    'name' => 'snapppay',
    'title' => 'اسنپ پی',
    'logo' => 'https://snapppay.ir/wp-content/uploads/2020/09/snapppay-logo.png',
    'is_active' => false,
    'settings' => json_encode([
     'username' => env('SNAPPAY_USERNAME', ''),
     'password' => env('SNAPPAY_PASSWORD', ''),
     'client_id' => env('SNAPPAY_CLIENT_ID', ''),
     'client_secret' => env('SNAPPAY_CLIENT_SECRET', ''),
     'currency' => 'T',
    ]),
   ],
  ];

  foreach ($gateways as $gateway) {
   DB::table('payment_gateways')->updateOrInsert(
    ['name' => $gateway['name']],
    $gateway
   );
  }
 }
}