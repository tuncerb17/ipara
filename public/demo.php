<?php header('Content-type: text/html; charset=utf-8');

include 'ipara_payment.php';

class DemoPage
{

    private $public_key = "ARENV2GB63QL6Y2";
    private $private_key = "ARENV2GB63QL6Y21NZB0780QQ";
//    private $vendor_id = 100;

    private $products = array();
    private $shipping_address = array();
    private $invoice_address = array();
    private $card = array();
    private $purchaser = array();

    function __construct()
    {
        $this->prepareProductArray();
        $this->prepareShippingAddress();
        $this->prepareInvoiceAddress();
        $this->prepareCard();
        $this->preparePurchaser();
    }

    public function renderDemoPageForm()
    {
        print("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">");
        print("<html>");
        print("<body><div style=\"width:600px;margin:0 auto;\">");

        $this->renderShoppingCard();
        $this->renderAddresses();
        $this->renderCard();
        $this->renderButton();
        print("</div></body>");
        print("</html>");
    }

    public function pay()
    {
        print("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">");
        print("<html>");
        print("<body><div style=\"width:350px;margin:0 auto;\">");

        date_default_timezone_set('Europe/Istanbul');

        // ödenecek toplam tutarın hesaplanması
        $amount = 0;
        foreach ($this->products as $product) {
            $amount = $amount + ($product['quantity'] * $product['price']);
        }

        // Odeme istek bilgilerinin olusturulmasi

        $obj = new iParaPayment();
        $obj->public_key = $this->public_key;
        $obj->private_key = $this->private_key;
        $obj->mode = "T";
        $obj->order_id = uniqid();
        $obj->installment = 1;
        $obj->amount = $amount;
//        $obj->vendor_id = $this->vendor_id;
        $obj->echo = "echo message";
        $obj->products = $this->products;
        $obj->shipping_address = $this->shipping_address;
        $obj->invoice_address = $this->invoice_address;
        $obj->card = $this->card;
        $obj->purchaser = $this->purchaser;

        $response = array();
        try {
            // Odeme bilgileri API ile odeme servisine iletilir.
            $response = $obj->pay();
        } catch (Exception $e) {
            print "<span style=\"font-weight:bold\">ÖDEME İŞLEMİNİZ BAŞARISIZ</span>";
            print '</br>' . $e->getMessage();
            return;
        }

        if ($response['result'] == 1) {
            print "<span style=\"font-weight:bold\">ÖDEME İŞLEMİNİZ BAŞARILI</span>";
        } else {
            print "<span style=\"font-weight:bold\">ÖDEME İŞLEMİNİZ BAŞARISIZ</span>";
        }
        print "</br>";
        print "</br><span style=\"font-weight:bold\">result : </span>" . $response['result'];
        print "</br><span style=\"font-weight:bold\">order_id : </span>" . $response['order_id'];
        print "</br><span style=\"font-weight:bold\">amount : </span>" . $response['amount'];
        print "</br><span style=\"font-weight:bold\">mode : </span>" . $response['mode'];
        print "</br><span style=\"font-weight:bold\">public_key : </span>" . $response['public_key'];
        print "</br><span style=\"font-weight:bold\">echo : </span>" . $response['echo'];
        print "</br><span style=\"font-weight:bold\">error_code : </span>" . $response['error_code'];
        print "</br><span style=\"font-weight:bold\">error_message : </span>" . $response['error_message'];
        print "</br><span style=\"font-weight:bold\">transaction_date : </span>" . $response['transaction_date'];
        print "</br><span style=\"font-weight:bold\">hash : </span>" . $response['hash'];
        print("</div></body>");
        print("</html>");
    }

    // 3D Secure Odeme Istegi
    public function payThreeD()
    {
        date_default_timezone_set('Europe/Istanbul');

        // odenecek toplam tutarin hesaplanmasi
        $amount = 0;
        foreach ($this->products as $product) {
            $amount = $amount + ($product['quantity'] * $product['price']);
        }

        // 3D Secure istek bilgilerinin olusturulmasi

        $obj = new iParaPayment();
        $obj->private_key = $this->private_key;
        $obj->public_key = $this->public_key;
        $obj->mode = "T";
        $obj->order_id = uniqid();
        $obj->card = $this->card;
        $obj->installment = 1;
        $obj->amount = $amount;
        $obj->echo = "echo message";
        $obj->purchaser = $this->purchaser;
        $obj->success_url = "http://www.magazaniz.com/demo.php?type=response&three_d_response=success";
        $obj->failure_url = "http://www.magazaniz.com/demo.php?type=response&three_d_response=failure";
        $obj->payThreeD();
    }

    // Basarili 3D Secure Islemi Sonuc Sayfasi, Burada islem 3D Secure bilgileri ile API ile odeme servisine gonderilerek odeme islemi tamamlanir
    public function confirmThreeDPayment()
    {
        print("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">");
        print("<html>");
        print("<body><div style=\"width:350px;margin:0 auto;\">");

        date_default_timezone_set('Europe/Istanbul');

        $obj = new iParaPayment();

        $response = array();

        $obj->private_key = $this->private_key;
        $obj->public_key = $this->public_key;

        try {
            // iPara nin gonderdigi 3D Secure sonuc bilgileri request bilgisinden alinir
            $response = $obj->getThreeDResponse($_POST);
            if ($response['result'] != 1) {
                print "<span style=\"font-weight:bold\">ÖDEME İŞLEMİNİZ BAŞARISIZ</span>";
                return;
            }
        } catch (Exception $e) {
            print "<span style=\"font-weight:bold\">ÖDEME İŞLEMİNİZ BAŞARISIZ</span>";
            print '</br>' . $e->getMessage();
            return;
        }

        // Odeme bilgilerinin hazirlanmasi

        // odenecek toplam tutarin hesaplanmasi
        $amount = 0;
        foreach ($this->products as $product) {
            $amount = $amount + ($product['quantity'] * $product['price']);
        }

        $obj->mode = "T";
        $obj->three_d_secure_code = $response['three_d_secure_code'];
        $obj->order_id = $response['order_id'];
        $obj->amount = $amount;
        $obj->echo = "echo message";
//        $obj->vendor_id = $this->vendor_id;
        $obj->products = $this->products;
        $obj->shipping_address = $this->shipping_address;
        $obj->invoice_address = $this->invoice_address;
        $obj->purchaser = $this->purchaser;

        $response = array();
        try {
            // 3D Secure sonuc bilgileri API ile odeme servisine iletilir.
            $response = $obj->pay();
        } catch (Exception $e) {
            print "<span style=\"font-weight:bold\">ÖDEME İŞLEMİNİZ BAŞARISIZ</span>";
            print '</br>' . $e->getMessage();
            return;
        }

        if ($response['result'] == 1) {
            print "<span style=\"font-weight:bold\">ÖDEME İŞLEMİNİZ BAŞARILI</span>";
        } else {
            print "<span style=\"font-weight:bold\">ÖDEME İŞLEMİNİZ BAŞARISIZ</span>";
        }
        print "</br>";
        print "</br><span style=\"font-weight:bold\">result : </span>" . $response['result'];
        print "</br><span style=\"font-weight:bold\">order_id : </span>" . $response['order_id'];
        print "</br><span style=\"font-weight:bold\">amount : </span>" . $response['amount'];
        print "</br><span style=\"font-weight:bold\">mode : </span>" . $response['mode'];
        print "</br><span style=\"font-weight:bold\">public_key : </span>" . $response['public_key'];
        print "</br><span style=\"font-weight:bold\">echo : </span>" . $response['echo'];
        print "</br><span style=\"font-weight:bold\">error_code : </span>" . $response['error_code'];
        print "</br><span style=\"font-weight:bold\">error_message : </span>" . $response['error_message'];
        print "</br><span style=\"font-weight:bold\">transaction_date : </span>" . $response['transaction_date'];
        print "</br><span style=\"font-weight:bold\">hash : </span>" . $response['hash'];
        print("</div></body>");
        print("</html>");
    }

    // Hatali 3D Secure Islemi Sonuc Sayfasi
    public function showThreeDFailureReponse()
    {
        $obj = new iParaPayment();

        try {
            $obj->private_key = $this->private_key;
            $obj->public_key = $this->public_key;
            // iPara nin gonderdigi 3D Secure sonuc bilgileri request bilgisinden alinir
            $response = $obj->getThreeDResponse($_POST);
        } catch (Exception $e) {
            print "<span style=\"font-weight:bold\">ÖDEME İŞLEMİNİZ BAŞARISIZ</span>";
            print '</br>' . $e->getMessage();
            return;
        }

        print("<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">");
        print("<html>");
        print("<body><div style=\"width:400px;margin:0 auto;\">");
        if ($response['result'] == 1) {
            print "<span style=\"font-weight:bold\">ÖDEME İŞLEMİNİZ BAŞARILI</span>";
        } else {
            print "<span style=\"font-weight:bold\">ÖDEME İŞLEMİNİZ BAŞARISIZ</span>";
        }
        print "</br>";
        print "</br><span style=\"font-weight:bold\">result : </span>" . $response['result'];
        print "</br><span style=\"font-weight:bold\">orderId : </span>" . $response['order_id'];
        print "</br><span style=\"font-weight:bold\">amount : </span>" . $response['amount'];
        print "</br><span style=\"font-weight:bold\">mode : </span>" . $response['mode'];
        print "</br><span style=\"font-weight:bold\">publicKey : </span>" . $response['public_key'];
        print "</br><span style=\"font-weight:bold\">echo : </span>" . $response['echo'];
        print "</br><span style=\"font-weight:bold\">errorCode : </span>" . $response['error_code'];
        print "</br><span style=\"font-weight:bold\">errorMessage : </span>" . $response['error_message'];
        print "</br><span style=\"font-weight:bold\">transactionDate : </span>" . $response['transaction_date'];
        print "</br><span style=\"font-weight:bold\">hash : </span>" . $response['hash'];
        print("</div></body>");
        print("</html>");
    }

    // Urun Bilgileri. n adet olabilir.
    private function prepareProductArray()
    {
        $this->products[0]['title'] = "Product 1";
        $this->products[0]['code'] = "P0001";
        $this->products[0]['quantity'] = 2;
        $this->products[0]['price'] = 100.00;
        $this->products[1]['title'] = "Product 2";
        $this->products[1]['code'] = "P0002";
        $this->products[1]['quantity'] = 1;
        $this->products[1]['price'] = 50.00;
    }

    // Alici Kargo Adresi Bilgileri
    private function prepareShippingAddress()
    {
        $this->shipping_address = array();
        $this->shipping_address['name'] = "Murat";
        $this->shipping_address['surname'] = "Kaya";
        $this->shipping_address['address'] = "Mevlüt Pehlivan Mah. Multinet Plaza Şişli";
        $this->shipping_address['zipcode'] = 34782;
        $this->shipping_address['city_code'] = 34;
        $this->shipping_address['city_text'] = "İstanbul";
        $this->shipping_address['country_code'] = "TR";
        $this->shipping_address['country_text'] = "Türkiye";
        $this->shipping_address['phone_number'] = "2123886600";
    }

    // Alici Fatura Adresi Bilgileri
    private function prepareInvoiceAddress()
    {
        $this->invoice_address = array();
        $this->invoice_address['name'] = "Murat";
        $this->invoice_address['surname'] = "Kaya";
        $this->invoice_address['address'] = "Mevlüt Pehlivan Mah. Multinet Plaza Şişli";
        $this->invoice_address['zipcode'] = 34782;
        $this->invoice_address['city_code'] = 34;
        $this->invoice_address['city_text'] = "İstanbul";
        $this->invoice_address['country_code'] = "TR";
        $this->invoice_address['country_text'] = "Türkiye";
        $this->invoice_address['tc_certificate_number'] = 1234567890;
        $this->invoice_address['phone_number'] = "2123886600";
        $this->invoice_address['tax_number'] = 123456;
        $this->invoice_address['tax_office'] = "Kozyatağı";
        $this->invoice_address['company_name'] = "iPara";
    }

    // Alici Kart Bilgileri
    private function prepareCard()
    {
        $this->card = array();
        $this->card['owner_name'] = "Murat Kaya";
        $this->card['number'] = "4282209027132016";
        $this->card['expire_month'] = "05";
        $this->card['expire_year'] = "17";
        $this->card['cvc'] = "232";
    }

    // Alici Bilgileri
    private function preparePurchaser()
    {
        $this->purchaser['name'] = "Murat";
        $this->purchaser['surname'] = "Kaya";
        $this->purchaser['birthdate'] = "1982-07-11";
        $this->purchaser['email'] = "murat@kaya.com";
        $this->purchaser['gsm_number'] = "5881231212";
        $this->purchaser['tc_certificate_number'] = 1234567890;
    }

    private function renderShoppingCard()
    {
        print("<fieldset><legend><label style=\"font-weight:bold;width:250px;\">Sepet Bilgileri</label></legend>");
        print("<table style=\"margin: 0 auto;\" border=\"1\"><tr><th width=\"100px;\">Ürün</th><th width=\"100px;\">Kod</th><th width=\"100px;\">Adet</th><th width=\"100px;\">Birim Fiyat</th></tr>");
        $total = 0;
        foreach ($this->products as $product) {
            print("<tr style=\"text-align:center;\"><td>" . $product['title'] . "</td><td>" . $product['code'] . "</td><td>" . $product['quantity'] . "</td><td>" . number_format((float)$product['price'], 2, '.', '') . " TL</td></tr>");
            $total = $total + ($product['quantity'] * $product['price']);
        }
        print("<tr><td colspan=\"3\" style=\"text-align:right;font-weight:bold;\">Toplam Tutar</td><td style=\"text-align:center;\">" . number_format((float)$total, 2, '.', '') . " TL</td></tr>");
        print("</table>");
        print("</fieldset>");
    }

    private function renderAddresses()
    {
        print("<div style=\"width:300px;float:left;\">");
        print("<fieldset><legend><label style=\"font-weight:bold;width:250px;\">Kargo Adresi Bilgileri</label></legend>");
        print("<label style=\"font-weight:bold;\">Ad : </label>" . $this->shipping_address['name'] . "</br>");
        print("<label style=\"font-weight:bold;\">Soyad : </label>" . $this->shipping_address['surname'] . "</br>");
        print("<label style=\"font-weight:bold;\">Adres : </label>" . $this->shipping_address['address'] . "</br>");
        print("<label style=\"font-weight:bold;\">Posta Kodu : </label>" . $this->shipping_address['zipcode'] . "</br>");
        print("<label style=\"font-weight:bold;\">Şehir : </label>" . $this->shipping_address['city_text'] . "</br>");
        print("<label style=\"font-weight:bold;\">Ülke : </label>" . $this->shipping_address['country_text'] . "</br>");
        print("<label style=\"font-weight:bold;\">Telefon Numarası: </label>" . $this->shipping_address['phone_number'] . "</br>");
        print("</fieldset>");
        print("</div>");
        print("<div style=\"width:300px;float:left;\">");
        print("<fieldset><legend><label style=\"font-weight:bold;width:250px;\">Fatura Adresi Bilgileri</label></legend>");
        print("<label style=\"font-weight:bold;\">Ad : </label>" . $this->invoice_address['name'] . "</br>");
        print("<label style=\"font-weight:bold;\">Soyad : </label>" . $this->invoice_address['surname'] . "</br>");
        print("<label style=\"font-weight:bold;\">Adres : </label>" . $this->invoice_address['address'] . "</br>");
        print("<label style=\"font-weight:bold;\">Posta Kodu : </label>" . $this->invoice_address['zipcode'] . "</br>");
        print("<label style=\"font-weight:bold;\">Şehir : </label>" . $this->invoice_address['city_text'] . "</br>");
        print("<label style=\"font-weight:bold;\">Ülke : </label>" . $this->invoice_address['country_text'] . "</br>");
        print("<label style=\"font-weight:bold;\">TC Kimlik Numarası : </label>" . $this->invoice_address['tc_certificate_number'] . "</br>");
        print("<label style=\"font-weight:bold;\">Telefon Numarası: </label>" . $this->invoice_address['phone_number'] . "</br>");
        print("<label style=\"font-weight:bold;\">Vergi Numarası : </label>" . $this->invoice_address['tax_number'] . "</br>");
        print("<label style=\"font-weight:bold;\">Vergi Dairesi Adı : </label>" . $this->invoice_address['tax_office'] . "</br>");
        print("<label style=\"font-weight:bold;\">Firma Adı: </label>" . $this->invoice_address['company_name']);
        print("</fieldset>");
        print("</div>");
    }

    private function renderCard()
    {
        print("<fieldset><legend><label style=\"font-weight:bold;width:250px;\">Kart Bilgileri</label></legend>");
        print("<label style=\"font-weight:bold;\">Kart Sahibinin Adı : </label>" . $this->card['owner_name'] . "</br>");
        print("<label style=\"font-weight:bold;\">Kart Numarası : </label>" . $this->card['number'] . "</br>");
        print("<label style=\"font-weight:bold;\">Son Kullanma Tarihi : </label>" . $this->card['expire_month'] . " / " . $this->card['expire_year'] . "</br>");
        print("<label style=\"font-weight:bold;\">Güvenlik Kodu : </label>" . $this->card['cvc'] . "</br>");
        print("</fieldset>");
    }

    private function renderButton()
    {
        print("<form action=\"demo.php?type=pay\" method=\"post\" id=\"iParaPaymentForm\">");
        print("<input type=\"checkbox\" name=\"three_d\">3D-Secure</input>");
        print("<input type=\"submit\" value=\"Öde\" style=\"float:right;font-weight:bold; padding:5 5 5 5;width:50px;\"/>");
        print("</form>");
    }
}

// ../demo.php 											| DEMO ANA SAYFASI
// ../demo.php?type=pay 								| ÖDEME URL İ
// ../demo.php?type=response&three_d_response=success 	| 3D ÖDEME BAŞARILI CEVAP URL İ
// ../demo.php?type=response&three_d_response=failure 	| 3D ÖDEME BAŞARISIZ CEVAP URL İ
$demo_page = new DemoPage();
if (isset($_GET['type'])) {
    $ftype = strtolower($_GET['type']);
    if ($ftype == "pay") {
        if (isset($_POST['three_d'])) {
            $demo_page->payThreeD();
        } else {
            $demo_page->pay();
        }
    } else if ($ftype == "response") {
        $ftype = strtolower($_GET['three_d_response']);
        if ($ftype == "success") {
            $demo_page->confirmThreeDPayment();
        } else if ($ftype == "failure") {
            $demo_page->showThreeDFailureReponse();
        }
    }
} else {
    $demo_page->renderDemoPageForm();
}
?>