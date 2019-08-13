<?php
/**
 * Avail - Point of Sale
 *
 * @category  Avail
 * @package   Avail_Pointofsale
 * @copyright 2013 Avail
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Avail_Pointofsale_Helper_Data extends Mage_Core_Helper_Abstract
{
    const INIT_COOKIE_NAME = '__avail_cookies__';
    const SEARCH_PHRASE_KEY = 'search_phrase';
    const PAGE_VIEWED_KEY = 'page_viewed';
    const MAX_PAGE_VIEWED = 1;
    const LOG_FILENAME = 'avail-pointofsale.log';
    const BUY_BUTTON_URL_PARAM = 'buy';
    
    /**
     * Add the Js code to the registry stack for avail
     *
     * @return bool
     */
    public function addJsCode($script)
    {
        $js = Mage::registry('avail_js');
        
        Mage::unregister('avail_js');
        if (!is_array($js)){
            $js = array();
        }
        $js[] = $script;
        Mage::register('avail_js', $js);
        
        return $this;
    }

    /**
     * Collapse the avail Js code sections
     *
     * @return bool
     */
    public function getJsCode()
    {
        return implode(PHP_EOL, (array)Mage::registry('avail_js'));
    }

    /**
     * Retrieve the suitable protocol
     *
     * @return bool
     */
    public function getProtocol()
    {
        return Mage::app()->getStore()->isCurrentlySecure() ? 'https' : 'http';
    }
    
    /**
     * Return if there's a default configuration
     *
     * @return bool
     */
    public function isConfigured()
    {
        return (bool)(Mage::getStoreConfig('pointofsale/account/customer_id') && Mage::getStoreConfig('pointofsale/account/password'));
    }
    
    /**
     * Retrieve all the handles used to render a page
     * 
     * @return array
     */
    public function getHandles() 
    {
        return Mage::app()->getFrontController()->getAction()->getLayout()->getUpdate()->getHandles();
    }
    
    /**
     * Retrieve JSON-RPC Server URL
     * 
     * @return string
     */
    public function getJsonRpcServerUrl()
    {
        $url = '';
        $url = Mage::getStoreConfig('pointofsale/interface/url');
        $url = (substr($url, -1) === '/') ? $url : $url . '/';
        $url = str_replace('http', $this->getProtocol(), $url);
        $url .= Mage::getStoreConfig('pointofsale/account/customer_id') . '/services/jsonrpc-1.x/';
        
        return $url;
    }
    
    /**
     * Return if the debug mode is allowed or not
     *
     * @return bool
     */
    public function isDebugMode()
    {
        return (bool)Mage::getStoreConfig('pointofsale/interface/debug');
    }
    
    /**
     * Add logs to the Avail Point of Sale log file
     * 
     * @param string $message
     */
    public function log($message)
    {
        Mage::log($message, null, self::LOG_FILENAME);
    }
    
    /**
     * Returns buy button url parameter
     * 
     * @return string
     */
    public function getBuyButtonUrlParam()
    {
        return self::BUY_BUTTON_URL_PARAM;
    }
    
    /**
     * Retrieve Avail main cookie name
     * 
     * @return string
     */
    public function getInitCookieName()
    {
        return self::INIT_COOKIE_NAME;
    }
    
    /**
     * Returns search phrase key used in the main cookie
     * 
     * @return string
     */
    public function getSearchPhraseKey()
    {
        return self::SEARCH_PHRASE_KEY;
    }
    
    /**
     * Returns total viewed pages key used in the main cookie
     * 
     * @return string
     */
    public function getPageViewedKey()
    {
        return self::PAGE_VIEWED_KEY;
    }
    
    /**
     * Gets max total pages allowed to visit before triggering the saveSearch
     * 
     * @return int
     */
    public function getMaxPageViewed()
    {
        return self::MAX_PAGE_VIEWED;
    }
    
    /**
     * Returns add to cart url including buy button parameter
     * 
     * @return string
     */
    public function getAddToCartUrl()
    {
        return Mage::getUrl('checkout/cart/add', array('_secure' => true));
    }
}
