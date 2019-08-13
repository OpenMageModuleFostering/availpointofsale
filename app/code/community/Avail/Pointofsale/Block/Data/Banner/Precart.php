<?php
/**
 * Avail - Point of Sale
 *
 * @category  Avail
 * @package   Avail_Pointofsale
 * @copyright 2013 Avail
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Avail_Pointofsale_Block_Data_Banner_Precart extends Avail_Pointofsale_Block_Data_Banner
{
    /**
     * Path to the banner name in sys configuration
     * 
     * @var string
     */
    protected $_xmlPathToBanner = 'pointofsale/template/precart';
    
    /**
     * Retrieve Last Added to Cart Product Id
     *
     * @return int $porductId
     */
    protected function _getLastAddedToCartProductId()
    {
        $productId = '';
        $quote = $this->helper('checkout')->getQuote();
        $items = $quote->getAllVisibleItems();
        $lastItem = array_pop($items);
        
        if ($lastItem && $lastItem->getProductId()) {
            $productId = $lastItem->getProductId();
        }
        
        return $productId;
    }
    
    /**
     * Return the Avail cart banner
     *
     * @return bool
     */
    protected function _toHtml()
    {
        $html = '';
        $bannerName = $this->getBannerName();
        $lastAddedToCartProductId = $this->_getLastAddedToCartProductId();
        
        if (!empty($bannerName)) {
            $html = '<div data-id="avail" data-banner="' . $bannerName . '" data-productid="' . $lastAddedToCartProductId . '"></div>';
        }
        
        return $html;
    }
}
