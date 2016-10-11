<?php

namespace App\Http\Controllers;

use Omnipay\Omnipay;

class IparaController extends Controller
{
    public function index()
    {
        $gateway =  new Gateway();

        $gateway->setMode('T');
        $gateway->setPublicKey('XXX');
        $gateway->setPrivateKey('XXX');

        $card = [
            'owner_name' => "Murat Kaya",
            'number' => "4282209027132016",
            'expire_month' => "05",
            'expire_year' => "17",
            'cvc' => "232",
        ];
        $response = $gateway->purchase([
            'order_id' => '17',
            'amount' => '1.00',
            'card' => $card
        ])->send();


        if($response->isSuccessful()){
            return $response->getData();
        }else {
            return $response->getMessage();
        }
    }
}
