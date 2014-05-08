<?php

namespace baibaratsky\WebMoney\Api\X\X6;

use baibaratsky\WebMoney\Api\X;
use baibaratsky\WebMoney\Exception\ApiException;
use baibaratsky\WebMoney\Request\RequestSigner;
use baibaratsky\WebMoney\Request\RequestValidator;

/**
 * Class Request
 *
 * @link http://wiki.wmtransfer.com/projects/webmoney/wiki/Interface_X6
 */
class Request extends X\Request
{
    /** @var string wmid */
    protected $signerWmid;

    /** @var string message\receiverwmid */
    protected $messageRecepientWmid;

    /** @var string message\msgsubj */
    protected $messageSubject;

    /** @var string message\msgtext */
    protected $messageText;

    /** @var int message\onlyauth */
    protected $messageOnlyAuth;

    /**
     * @param string $authType
     *
     * @throws ApiException
     */
    public function __construct($authType = self::AUTH_CLASSIC)
    {
        if ($authType === self::AUTH_CLASSIC) {
            $this->url = 'https://w3s.wmtransfer.com/asp/XMLSendMsg.asp';
        } elseif ($authType === self::AUTH_LIGHT) {
            $this->url = 'https://w3s.wmtransfer.com/asp/XMLSendMsgCert.asp';
        } else {
            throw new ApiException('This interface doesn\'t support the authentication type given.');
        }

        parent::__construct($authType);
    }

    /**
     * @return array
     */
    protected function getValidationRules()
    {
        return array(
            RequestValidator::TYPE_REQUIRED => array(
                'messageRecepientWmid', 'messageSubject', 'messageText',
            ),
            RequestValidator::TYPE_DEPEND_REQUIRED => array(
                'signerWmid' => array('authType' => array(self::AUTH_CLASSIC)),
            ),
        );
    }

    /**
     * @return string
     */
    public function getData()
    {
        $xml = '<w3s.request>';
        $xml .= self::xmlElement('reqn', $this->requestNumber);
        $xml .= self::xmlElement('wmid', $this->signerWmid);
        $xml .= self::xmlElement('sign', $this->signature);
        $xml .= '<message>';
        $xml .= self::xmlElement('receiverwmid', $this->messageRecepientWmid);
        $xml .= self::xmlElement('msgsubj', $this->messageSubject);
        $xml .= self::xmlElement('msgtext', $this->messageText);
        $xml .= self::xmlElement('onlyauth', $this->messageOnlyAuth);
        $xml .= '</message>';
        $xml .= '</w3s.request>';

        return $xml;
    }

    /**
     * @return string
     */
    public function getResponseClassName()
    {
        return Response::className();
    }

    /**
     * @param RequestSigner $requestSigner
     */
    public function sign(RequestSigner $requestSigner = null)
    {
        if ($this->authType === self::AUTH_CLASSIC) {
            $this->signature = $requestSigner->sign($this->messageRecepientWmid . $this->requestNumber .
                $this->messageText . $this->messageSubject);
        }
    }
}