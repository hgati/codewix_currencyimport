<?php
/**
 * Description of Currencyimport
 * @package   Codewix_Currencyimport
 * @company   Codewix - http://www.codewix.com/
 * @author    Ravinder <codewix@gmail.com>
 */
class Codewix_Currencyimport_Model_Currency_Import_Google extends Mage_Directory_Model_Currency_Import_Abstract {

    protected $_url = 'http://www.google.com/finance/converter?a=1&from={{CURRENCY_FROM}}&to={{CURRENCY_TO}}';
    protected $_messages = array();

    /**
     * HTTP client
     * @var Varien_Http_Client
     */
    protected $_httpClient;

    public function __construct()
    {
        $this->_httpClient = new Varien_Http_Client();
    }

    protected function _convert($currencyFrom, $currencyTo, $retry=0)
    {
        $url = str_replace('{{CURRENCY_FROM}}', $currencyFrom, $this->_url);
        $url = str_replace('{{CURRENCY_TO}}', $currencyTo, $url);

        try {
            $response = $this->_httpClient
                ->setUri($url)
                ->request('GET')
                ->getBody();

            if(preg_match("'<span class=bld>([0-9\.]+)\s\w+</span>'", $response, $array))
                $result = $array[1];

            if( !$result) {
                $this->_messages[] = Mage::helper('directory')->__('Cannot retrieve rate from %s', $this->_url);
                return null;
            }

            return (float) $result * 1.0;
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