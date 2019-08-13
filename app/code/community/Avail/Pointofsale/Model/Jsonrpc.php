<?php
/**
 * Avail - Point of Sale
 *
 * @category  Avail
 * @package   Avail_Pointofsale
 * @copyright 2013 Avail
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Avail_Pointofsale_Model_Jsonrpc
{
    const SESSION_ID_COOKIE_NAME = '__avail_session__';
    
    protected $_httpClient;
    protected $_helper;
    
    public function __construct()
    {
        $this->_helper = Mage::helper('pointofsale');
        
        try {
            $this->_httpClient = new Zend_Http_Client();
            $this->_httpClient->setUri($this->_helper->getJsonRpcServerUrl())
                        ->setMethod(Zend_Http_Client::POST);
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
    }
    
    /**
     * Retrieve Avail session id from the cookie
     * 
     * @return string
     */
    protected function _getSessionId()
    {
        return Mage::getModel('core/cookie')->get(self::SESSION_ID_COOKIE_NAME);
    }
    
    /**
     * Log using JSON-RPC the product added to cart
     * 
     * @param int $productId
     * @return string
     */
    public function logAddedToCart($productId)
    {
        $params = array(
            'method' => 'logAddedToCart',
            'params' => array(
                'SessionID' => $this->_getSessionId(), 
                'ProductID' => (string)$productId
            ),
            'version' => '1.1',
            'id' => md5(md5(time()))
        );
        
        return $this->_getBody($params);
    }
    
    /**
     * Log using JSON-RPC the product removed from cart
     * 
     * @param int $productId
     * @return string
     */
    public function logRemovedFromCart($productId)
    {
        $params = array(
            'method' => 'logRemovedFromCart',
            'params' => array(
                'SessionID' => $this->_getSessionId(), 
                'ProductID' => (string)$productId
            ),
            'version' => '1.1',
            'id' => md5(md5(time()))
        );
        
        return $this->_getBody($params);
    }
    
    /**
     * 
     * 
     * @param array $params
     * @return string
     */
    protected function _getBody($params)
    {
        $this->_httpClient->setRawData(json_encode($params));
        $result = $this->_httpClient->request()->getBody();
        
        if ($this->_helper->isDebugMode()) {
            $this->_helper->log(json_encode($params));
            $this->_helper->log($result);
        }
        
        return $result;
    }
    
    /**
     * Sends order information to Avail Servers using logPurchase call
     * 
     * @param int $orderId
     * @return string
     */
    public function logPurchase($orderId)
    {
        $order = Mage::getModel('sales/order')->load($orderId);
        
        if ($order instanceof Mage_Sales_Model_Order && $order->getId()) {
            
            $productIds = array();
            $productPrices = array();

            foreach ($order->getAllItems() as $item) {
                if (!$item->getParentItemId()) {
                    for ($i = 0; $i < $item->getQtyOrdered(); $i++) {
                        $productIds[] = $item->getProductId();
                        $productPrices[] = $item->getBasePrice();
                    }
                }
            }
            
           $params = array(
                'method' => 'logPurchase',
                'params' => array(
                    'SessionID' => $this->_getSessionId(),
                    'UserID' => $this->_getHashedCustomerId($order),
                    'ProductIDs' => $productIds,
                    'Prices' => $productPrices,
                    'OrderID' => $order->getIncrementId(),
                    'Currency' => $this->_getCurrency($order)
                ),
                'version' => '1.1',
                'id' => md5(md5(time()))
            );

            return $this->_getBody($params); 
        }
    }
    
    /**
     * Returns hashed customer email
     * We use the email and not customer id because of Guest/Registered mode
     * When purchasing with guest mode, no customer is created, so no customer id  
     * 
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    protected function _getHashedCustomerId($order)
    {
        return Mage::helper('core')->getHash($order->getCustomerEmail());
    }
    
    /**
     * Retrieve order currency
     * 
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    protected function _getCurrency($order)
    {
        return $order->getStore()->getCurrentCurrency()->toString();
    }
    
    /**
     * Sends search phrase and product id to Avail
     * 
     * @param string $searchPhrase
     * @param int $productId
     * @return string
     */
    public function saveSearch($searchPhrase, $productId)
    {
        $params = array(
            'method' => 'saveSearch',
            'params' => array(
                'SearchPhrase' => $searchPhrase,
                'ProductID' => (string)$productId
            ),
            'version' => '1.1',
            'id' => md5(md5(time()))
        );
        
        return $this->_getBody($params);
    }
}
