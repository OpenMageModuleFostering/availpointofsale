<?php
/**
 * Avail - Point of Sale
 *
 * @category  Avail
 * @package   Avail_Pointofsale
 * @copyright 2013 Avail
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Avail_Pointofsale_Model_Observer
{
    /**
     * Avail log purchase operation
     * 
     * @param Varien_Event_Observer $observer
     * @return \Avail_Pointofsale_Model_Observer
     */
    public function logPurchase(Varien_Event_Observer $observer)
    {
        $orderIds = $observer->getEvent()->getOrderIds();
        $api = Mage::getSingleton('pointofsale/jsonrpc');
        
        foreach ($orderIds as $orderId) {
            $api->logPurchase($orderId);
        }
        
        return $this;
    }
    
    /**
     * Log the added product id to cart
     * 
     * /!\ if the 'buy' button was clicked to add a product and this product
     * has options, so you're redirected to the product page, the log won't be
     * triggered again if you click on 'add to cart'
     * 
     * @param Varien_Event_Observer $observer
     * @return \Avail_Pointofsale_Model_Observer
     */
    public function logAddedToCart(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('pointofsale');
        
        $jsonCookie = json_decode(
                $observer->getEvent()->getRequest()->getCookie($helper->getInitCookieName())
        );
        
        $buyProductId = isset($jsonCookie->{$helper->getBuyButtonUrlParam()}) ? 
                            $jsonCookie->{$helper->getBuyButtonUrlParam()} : 
                            0;
        
        $product = $observer->getEvent()->getProduct();
        $api = Mage::getSingleton('pointofsale/jsonrpc');

        if ($product && $product->getId() && ($product->getId() != $buyProductId)) {
            $api->logAddedToCart($product->getId());
        }
        
        return $this;
    }
    
    /**
     * Log the removed product id from the cart
     *
     * @param Varien_Event_Observer $observer
     * @return \Avail_Pointofsale_Model_Observer
     */
    public function logRemovedFromCart(Varien_Event_Observer $observer)
    {
        $api = Mage::getSingleton('pointofsale/jsonrpc');
        $itemId = Mage::app()->getFrontController()->getRequest()->get('id');
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        
        if ($quote && $quote->getId() && $itemId) {
            $item = $quote->getItemById($itemId);
            if ($item && $item->getProductId()) {
                $api->logRemovedFromCart($item->getProductId());
            } 
        }
        
        return $this;
    }
    
    /**
     * Sends JS to browser to save search phrase in a cookie
     * 
     * @return \Avail_Pointofsale_Model_Observer
     */
    public function saveSearchPhrase()
    {
        $searchPhrase = Mage::helper('catalogSearch')->getQueryText();
        $escapedSearchPhrase = Mage::helper('core')->jsQuoteEscape($searchPhrase, '"');
        
        $script = 'Cookie.setData("' . 
                Avail_Pointofsale_Helper_Data::SEARCH_PHRASE_KEY . 
                '", "' . 
                $searchPhrase .
                '");';
        
        $script .= 'Cookie.setData("' . 
                Avail_Pointofsale_Helper_Data::PAGE_VIEWED_KEY . 
                '", 0);';
        
        Mage::helper('pointofsale')->addJsCode($script);
        return $this;
    }
    
    /**
     * Log saveSearch call to Avail server if there is a search phrase and 
     * the visited pages are equal or less than one
     * 
     * @param Varien_Event_Observer $observer
     * @return \Avail_Pointofsale_Model_Observer
     */
    public function saveSearch(Varien_Event_Observer $observer)
    {
        $api = Mage::getSingleton('pointofsale/jsonrpc');
        $product = $observer->getEvent()->getProduct();
        $helper = Mage::helper('pointofsale');
        $max = $helper->getMaxPageViewed();
        
        $jsonCookie = json_decode(
                $observer->getEvent()->getRequest()->getCookie($helper->getInitCookieName())
        );
        
        $searchPhrase = isset($jsonCookie->{$helper->getSearchPhraseKey()}) ? 
                            $jsonCookie->{$helper->getSearchPhraseKey()} : 
                            '';
        $pageViewed = isset($jsonCookie->{$helper->getPageViewedKey()}) ? 
                            $jsonCookie->{$helper->getPageViewedKey()} : 
                            $max++;
        
        if (!empty($searchPhrase) && $pageViewed <= $max && $product && $product->getId()) {
            $api->saveSearch($searchPhrase, $product->getId());
        }
        
        return $this;
    }
}
