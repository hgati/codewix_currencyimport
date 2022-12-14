<?php
class Codewix_Currencyimport_Model_Currency_Import_Openapi extends Mage_Directory_Model_Currency_Import_Abstract {
    protected $_url = 'http://free.currencyconverterapi.com/api/v3/convert?q={{CURRENCY_FROM}}_{{CURRENCY_TO}}';
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
			$resultKey = $currencyFrom.'_'.$currencyTo;
            $response = $this->_httpClient
                ->setUri($url)
                ->request('GET')
                ->getBody();

			$data = Mage::helper('core')->jsonDecode($response);
			$results = $data['results'][$resultKey];
			$queryCount = $data['query']['count'];

			if( !$queryCount &&  !isset($results)) {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s.', $url);
                return null;
	    	}

            return (float) $result['val'] * 1.0;
        }
        catch (Exception $e) {
            if( $retry == 0 ) {
                $this->_convert($currencyFrom, $currencyTo, 1);
            } else {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s.', $url);
            }
        }
    }
}