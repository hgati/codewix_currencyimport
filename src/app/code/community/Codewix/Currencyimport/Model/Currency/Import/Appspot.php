<?php
class Codewix_Currencyimport_Model_Currency_Import_Appspot extends Mage_Directory_Model_Currency_Import_Abstract {
    protected $_url = 'https://currency-api.appspot.com/api/{{CURRENCY_FROM}}/{{CURRENCY_TO}}.json';
    protected $_messages = array();

	/**
     * HTTP client
     * @var Varien_Http_Client
     */
    protected $_httpClient;
    public function __construct() {
        $this->_httpClient = new Varien_Http_Client();
    }

    protected function _convert($currencyFrom, $currencyTo, $retry=0) {
        $url = str_replace('{{CURRENCY_FROM}}', $currencyFrom, $this->_url);
        $url = str_replace('{{CURRENCY_TO}}', $currencyTo, $url);

        try {
            $response = $this->_httpClient
                ->setUri($url)
                ->request('GET')
                ->getBody();

			$result = Mage::helper('core')->jsonDecode($response);
			if(!$result['success']) {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s.', $url);
                return null;
	    	}

            return (float) $result['rate'] * 1.0;
        } catch (Exception $e) {
            if( $retry == 0 ) {
                $this->_convert($currencyFrom, $currencyTo, 1);
            } else {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s.', $url);
            }
        }
    }
}