<?php
/**
 * Created by PhpStorm.
 * User: tuncer
 * Date: 11/10/16
 * Time: 10:16
 */

namespace App\Http\Controllers;


use Omnipay\Common\AbstractGateway;


/**
 * @method \Omnipay\Common\Message\ResponseInterface authorize(array $options = array())
 * @method \Omnipay\Common\Message\ResponseInterface completeAuthorize(array $options = array())
 * @method \Omnipay\Common\Message\ResponseInterface capture(array $options = array())
 * @method \Omnipay\Common\Message\ResponseInterface completePurchase(array $options = array())
 * @method \Omnipay\Common\Message\ResponseInterface refund(array $options = array())
 * @method \Omnipay\Common\Message\ResponseInterface void(array $options = array())
 * @method \Omnipay\Common\Message\ResponseInterface createCard(array $options = array())
 * @method \Omnipay\Common\Message\ResponseInterface updateCard(array $options = array())
 * @method \Omnipay\Common\Message\ResponseInterface deleteCard(array $options = array())
 */
class Gateway extends AbstractGateway
{

    /**
     * Get gateway display name
     *
     * This can be used by carts to get the display name for each gateway.
     */
    public function getName()
    {
        return 'ipara';
    }


    public function getDefaultParameters()
    {
        return array(
            'mode' => 'T',
            'installment' => '1',
            'amount' => '0',

        );
    }

    function __call($name, $arguments)
    {
        // TODO: Implement @method \Omnipay\Common\Message\ResponseInterface authorize(array $options = array())
        // TODO: Implement @method \Omnipay\Common\Message\ResponseInterface completeAuthorize(array $options = array())
        // TODO: Implement @method \Omnipay\Common\Message\ResponseInterface capture(array $options = array())
        // TODO: Implement @method \Omnipay\Common\Message\ResponseInterface purchase(array $options = array())
        // TODO: Implement @method \Omnipay\Common\Message\ResponseInterface completePurchase(array $options = array())
        // TODO: Implement @method \Omnipay\Common\Message\ResponseInterface refund(array $options = array())
        // TODO: Implement @method \Omnipay\Common\Message\ResponseInterface void(array $options = array())
        // TODO: Implement @method \Omnipay\Common\Message\ResponseInterface createCard(array $options = array())
        // TODO: Implement @method \Omnipay\Common\Message\ResponseInterface updateCard(array $options = array())
        // TODO: Implement @method \Omnipay\Common\Message\ResponseInterface deleteCard(array $options = array())
    }


    public function purchase(array $parameters = array()) {
        return $this->createRequest('\App\Http\Controllers\Message\PurchaseRequest', $parameters);
    }

    public function getMode()
    {
        return $this->getParameter('mode');
    }

    public function setMode($value)
    {
        return $this->setParameter('mode', $value);
    }


    public function getPublicKey() {
        return $this->getParameter('public_key');
    }
    public function setPublicKey($value) {
        return $this->setParameter('public_key', $value);
    }

    public function getPrivateKey() {
        return $this->getParameter('private_key');
    }
    public function setPrivateKey($value) {
        return $this->setParameter('private_key', $value);
    }

}