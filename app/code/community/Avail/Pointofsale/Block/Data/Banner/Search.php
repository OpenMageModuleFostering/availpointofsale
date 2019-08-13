<?php
/**
 * Avail - Point of Sale
 *
 * @category  Avail
 * @package   Avail_Pointofsale
 * @copyright 2012 Avail
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Avail_Pointofsale_Block_Data_Banner_Search extends Avail_Pointofsale_Block_Data_Banner
{
    /**
     * Path to the banner name in sys configuration
     * 
     * @var string
     */
    protected $_xmlPathToBanner = 'pointofsale/template/search';
    
    /**
     * Return the Avail search banner
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = '';
        $searchText = Mage::helper('catalogSearch')->getQuery()->getQueryText();
        $bannerName = $this->getBannerName();
        
        if (!empty($bannerName)) {
            $html = '<div data-id="avail" data-banner="' . $bannerName . '" data-phrase="' . $searchText . '"></div>';
        }
        
        return $html;
    }
}
