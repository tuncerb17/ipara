<?php

namespace App\Http\Controllers\Message;


use Omnipay\Common\Message\AbstractRequest;
use SimpleXMLElement;

/**
 * Created by PhpStorm.
 * User: tuncer
 * Date: 11/10/16
 * Time: 10:21
 */
class PurchaseRequest extends AbstractRequest
{

    private $public_key = "";
    private $private_key = "";

    function __construct()
    {
        $this->prepareProductArray();
        $this->prepareShippingAddress();
        $this->prepareInvoiceAddress();
        //$this->prepareCard();
        $this->preparePurchaser();
    }

    private $auth_url = "https://api.ipara.com/rest/payment/auth";
    private $three_d_url = "https://www.ipara.com/3dgate";
    private $version = "1.0";

    // Istek degiskenleri
    private $mode;
    private $three_d;
    private $order_id;
    private $installment;
    private $amount;
    private $vendor_id;
    private $echo;
    private $success_url;
    private $failure_url;
    private $three_d_secure_code;

    private $products = array();
    private $shipping_address = array();
    private $invoice_address = array();
    private $card = array();
    private $purchaser = array();

    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     */
    public function getData()
    {
        $this->mode = $this->getMode();
        $this->public_key = $this->getPublicKey();
        $this->private_key = $this->getPrivateKey();
        $this->card = $this->getCard();
        $this->amount = $this->getAmount();
        $this->order_id = $this->getOrderId();

        $this->installment = $this->getInstallment();

        $xml_data_product_part = "";
        foreach ($this->products as $product) {
            $xml_data_product_part .= "<product>\n" .
                "	<productCode>" . urlencode($product['code']) . "</productCode>\n" .
                "	<productName>" . urlencode($product['title']) . "</productName>\n" .
                "	<quantity>" . $product['quantity'] . "</quantity>\n" .
                "	<price>" . number_format((float)$product['price'], 2, '', '') . "</price>\n" .
                "</product>\n";
        }

        $three_d_secure_code_part = "";
        if ($this->three_d == "true") {
            $three_d_secure_code_part = "    <threeDSecureCode>" . $this->three_d_secure_code . "</threeDSecureCode>\n";
        }

        $vendor_id_part = "";
        if ($this->vendor_id != NULL) {
            $vendor_id_part .= "    <vendorId>" . $this->vendor_id . "</vendorId>\n";
        }

        $xml_data = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
            "<auth>\n" .
            "    <threeD>" . $this->three_d . "</threeD>\n" .
            "    <orderId>" . $this->order_id . "</orderId>\n" .
            "    <amount>" . number_format((float)$this->amount, 2, '', '') . "</amount>\n" .
            "    <cardOwnerName>" . urlencode($this->card['owner_name']) . "</cardOwnerName>\n" .
            "    <cardNumber>" . $this->card['number'] . "</cardNumber>\n" .
            "    <cardExpireMonth>" . $this->card['expire_month'] . "</cardExpireMonth>\n" .
            "    <cardExpireYear>" . $this->card['expire_year'] . "</cardExpireYear>\n" .
            "    <installment>" . $this->installment . "</installment>\n" .
            "    <cardCvc>" . $this->card['cvc'] . "</cardCvc>\n" .
            "    <mode>" . $this->mode . "</mode>\n" .
            $three_d_secure_code_part .
            $vendor_id_part .
            "    <products>\n" .
            $xml_data_product_part .
            "    </products>\n" .
            "    <purchaser>\n" .
            "        <name>" . urlencode($this->purchaser['name']) . "</name>\n" .
            "        <surname>" . urlencode($this->purchaser['surname']) . "</surname>\n" .
            "        <birthDate>" . $this->purchaser['birthdate'] . "</birthDate>\n" .
            "        <email>" . $this->purchaser['email'] . "</email>\n" .
            "        <gsmNumber>" . urlencode($this->purchaser['gsm_number']) . "</gsmNumber>\n" .
            "        <tcCertificate>" . urlencode($this->purchaser['tc_certificate_number']) . "</tcCertificate>\n" .
            "        <clientIp>" . $this->get_client_ip() . "</clientIp>\n" .
            "        <invoiceAddress>\n" .
            "            <name>" . urlencode($this->invoice_address['name']) . "</name>\n" .
            "            <surname>" . urlencode($this->invoice_address['surname']) . "</surname>\n" .
            "            <address>" . urlencode($this->invoice_address['address']) . "</address>\n" .
            "            <zipcode>" . urlencode($this->invoice_address['zipcode']) . "</zipcode>\n" .
            "            <city>" . urlencode($this->invoice_address['city_code']) . "</city>\n" .
            "            <tcCertificate>" . urlencode($this->invoice_address['tc_certificate_number']) . "</tcCertificate>\n" .
            "            <country>" . urlencode($this->invoice_address['country_code']) . "</country>\n" .
            "            <taxNumber>" . urlencode($this->invoice_address['tax_number']) . "</taxNumber>\n" .
            "            <taxOffice>" . urlencode($this->invoice_address['tax_office']) . "</taxOffice>\n" .
            "            <companyName>" . urlencode($this->invoice_address['company_name']) . "</companyName>\n" .
            "            <phoneNumber>" . urlencode($this->invoice_address['phone_number']) . "</phoneNumber>\n" .
            "        </invoiceAddress>\n" .
            "        <shippingAddress>\n" .
            "            <name>" . urlencode($this->shipping_address['name']) . "</name>\n" .
            "            <surname>" . urlencode($this->shipping_address['surname']) . "</surname>\n" .
            "            <address>" . urlencode($this->shipping_address['address']) . "</address>\n" .
            "            <zipcode>" . urlencode($this->shipping_address['zipcode']) . "</zipcode>\n" .
            "            <city>" . urlencode($this->shipping_address['city_code']) . "</city>\n" .
            "            <country>" . urlencode($this->shipping_address['country_code']) . "</country>\n" .
            "            <phoneNumber>" . urlencode($this->shipping_address['phone_number']) . "</phoneNumber>\n" .
            "        </shippingAddress>\n" .
            "    </purchaser>\n" .
            "</auth>";
        return $xml_data;
    }

    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     * @return \Omnipay\Common\Message\ResponseInterface
     * @throws \Exception
     */
    public function sendData($data)
    {
        $this->three_d = "false";

        if (isset($this->three_d_secure_code)) {
            $this->three_d = "true";
        }

        $xml_data = $this->getData();
        $output = $this->calliParaAuthService($xml_data);
        if ($output == NULL) {
            throw new \Exception("Ödeme cevabı boş");
        }
        $response = $this->prepareResponse($output);
//        $this->validateResponse($response);

        $response['amount'] = number_format((float)($response['amount'] / 100), 2, '.', '');

        return $response = new Response($response,$this->private_key);
//        return $response = new Response($response);


        // TODO: Implement sendData() method.
    }

    private function get_client_ip()
    {
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';

        return $ipaddress;
    }

    private function validateResponse($response)
    {
        if ($response['hash'] != NULL) {
            $hash_text = $response['order_id'] . $response['result'] . $response['amount'] . $response['mode'] . $response['error_code'] .
                $response['error_message'] . $response['transaction_date'] . $response['public_key'] . $this->private_key;
            $hash = base64_encode(sha1($hash_text, true));
            if ($hash != $response['hash']) {
                throw new \Exception("Ödeme cevabı hash doğrulaması hatalı. [result : " . $response['result'] . ",error_code : " . $response['error_code'] . ",error_message : " . $response['error_message'] . "]");
            }
        } else {
            throw new \Exception("Ödeme cevabı hash doğrulaması hatalı.");
        }
    }

    private function prepareResponse($output)
    {
        $xml_response = new SimpleXMLElement($output);
        if ($xml_response == NULL) {
            throw new \Exception("Ödeme cevabı xml formatında değil");
        }
        $response = array();
        $response['result'] = $xml_response->result;
        $response['order_id'] = $xml_response->orderId;
        $response['amount'] = $xml_response->amount;
        $response['mode'] = $xml_response->mode;
        $response['public_key'] = $xml_response->publicKey;
        $response['echo'] = $xml_response->echo;
        $response['error_code'] = $xml_response->errorCode;
        $response['error_message'] = $xml_response->errorMessage;
        $response['transaction_date'] = $xml_response->transactionDate;
        $response['hash'] = $xml_response->hash;
        return $response;
    }

    private function calliParaAuthService($xml_data)
    {
        $timestamp = date("Y-m-d H:i:s");
        $token = "";
        if ($this->three_d == "false") {
            $hash_text = $this->private_key . $this->order_id . number_format((float)$this->amount, 2, '', '') . $this->mode . $this->card['owner_name'] .
                $this->card['number'] . $this->card['expire_month'] . $this->card['expire_year'] . $this->card['cvc'] .
                $this->purchaser['name'] . $this->purchaser['surname'] . $this->purchaser['email'] . $timestamp;
            $token = $this->public_key . ":" . base64_encode(sha1($hash_text, true));
        } else if ($this->three_d == "true") {
            $hash_text = $this->private_key . $this->order_id . number_format((float)$this->amount, 2, '', '') . $this->mode .
                $this->three_d_secure_code . $timestamp;
            $token = $this->public_key . ":" . base64_encode(sha1($hash_text, true));
        }
        $ch = curl_init($this->auth_url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/xml", "transactionDate: " . $timestamp, "version: " . $this->version, "token: " . $token));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
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

    public function getOrderId() {
        return $this->getParameter('orderid');
    }
    public function setOrderId($value) {
        return $this->setParameter('orderid', $value);
    }

    public function getAmount() {
        return $this->getParameter('amount');
    }
    public function setAmount($value) {
        return $this->setParameter('amount', $value);
    }

    public function getMode() {
        return $this->getParameter('mode');
    }
    public function setMode($value) {
        return $this->setParameter('mode', $value);
    }

    public function getPublicKey() {
        return $this->getParameter('public_key');
    }
    public function setPublicKey($value) {
        return $this->setParameter('public_key', $value);
    }

    public function getOwnerName() {
        return $this->getParameter('owner_name');
    }
    public function setOwnerName($value) {
        return $this->setParameter('owner_name', $value);
    }

    public function getNumber() {
        return $this->getParameter('number');
    }
    public function setNumber($value) {
        return $this->setParameter('number', $value);
    }

    public function getExpireMonth() {
        return $this->getParameter('expire_month');
    }
    public function setExpireMonth($value) {
        return $this->setParameter('expire_month', $value);
    }

    public function getExpireYear() {
        return $this->getParameter('expire_year');
    }
    public function setExpireYear($value) {
        return $this->setParameter('expire_year', $value);
    }

    public function getCvc() {
        return $this->getParameter('cvc');
    }
    public function setCvc($value) {
        return $this->setParameter('cvc', $value);
    }

    public function getInstallment() {
        return $this->getParameter('installment');
    }
    public function setInstallment($value) {
        return $this->setParameter('installment', $value);
    }

    public function getPrivateKey() {
        return $this->getParameter('private_key');
    }
    public function setPrivateKey($value) {
        return $this->setParameter('private_key', $value);
    }

    public function getCard() {
        return $this->getParameter('card');
    }
    public function setCard($value) {
        return $this->setParameter('card', $value);
    }

}