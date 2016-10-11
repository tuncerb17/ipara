<?php
/**
 * Created by PhpStorm.
 * User: tuncer
 * Date: 11/10/16
 * Time: 19:11
 */

namespace App\Http\Controllers\Message;


use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

class Response extends AbstractResponse implements RedirectResponseInterface
{

    protected $data;
    protected $private_key;

    /**
     * Response constructor.
     * @param $data
     */
    public function __construct($data, $private_key)
    {
        $this->data = $data;
        $this->private_key = $private_key;
    }


    /**
     * Is the response successful?
     *
     * @return boolean
     */
    public function isSuccessful()
    {
        if (isset($this->data["result"])) {
            return $this->data["result"] == '1' && $this->validateResponse($this->data, $this->private_key);
        } else {
            return false;
        }
    }

    /**
     * Gets the redirect target url.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        // TODO: Implement getRedirectUrl() method.
    }

    /**
     * Get the required redirect method (either GET or POST).
     *
     * @return string
     */
    public function getRedirectMethod()
    {
        // TODO: Implement getRedirectMethod() method.
    }

    /**
     * Gets the redirect form data array, if the redirect method is POST.
     *
     * @return array
     */
    public function getRedirectData()
    {
        // TODO: Implement getRedirectData() method.
    }

    /**
     * Gets the redirect form data array, if the redirect method is POST.
     *
     * @return array
     */
    public function getMessage()
    {
        return $this->data['error_message'];
    }


    private function validateResponse($data,$private_key)
    {
        if ($data['hash'] != NULL) {
            $hash_text = $data['order_id'] . $data['result'] . $data['amount'] . $data['mode'] . $data['error_code'] .
                $data['error_message'] . $data['transaction_date'] . $data['public_key'] . $private_key;
            $hash = base64_encode(sha1($hash_text, true));
            if ($hash != $data['hash']) {
                return false;
                throw new \Exception("Ödeme cevabı hash doğrulaması hatalı. [result : " . $data['result'] . ",error_code : " . $data['error_code'] . ",error_message : " . $data['error_message'] . "]");
            }
        } else {
            return false;
            throw new \Exception("Ödeme cevabı hash doğrulaması hatalı.");
        }

        return true;
    }
}