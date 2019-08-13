<?php
/**
 * Avail - Point of Sale
 *
 * @category  Avail
 * @package   Avail_Pointofsale
 * @copyright 2013 Avail
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Avail_Pointofsale_Block_Data_Banner_Product extends Avail_Pointofsale_Block_Data_Banner
{
    /**
     * Path to the banner name in sys configuration
     * 
     * @var string
     */
    protected $_xmlPathToBanner = 'pointofsale/template/product';
    
    /**
     * Retrieve Product Id
     *
     * @return int $porductId
     */
    protected function _getProductId()
    {
        $productId = '';
        $product = Mage::registry('product');

        if ($product && $product->getId()) {
            $productId = $product->getId();
        }

        return $productId;
    }

    /**
     * Test if the category is allowed to be feched
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = '';
        $bannerName = $this->getBannerName();
        
        if (!empty($bannerName)) {
            $html = '<div data-id="avail" data-banner="' . $bannerName . '" data-productid="' . $this->_getProductId() . '"></div>';
        }
        
        return $html;
    }
}
