<?php
/**
 * Avail - Point of Sale
 *
 * @category  Avail
 * @package   Avail_Pointofsale
 * @copyright 2012 Avail
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Avail_Pointofsale_Block_Data_Banner extends Mage_Core_Block_Template
{
    /**
     * Path to the banner name in sys configuration
     * 
     * @var string
     */
    protected $_xmlPathToBanner = '';
    
    /**
     * Retrieve the banner name from system configuration
     * 
     * @return string
     */
    public function getBannerName() 
    {
        return Mage::getStoreConfig($this->_xmlPathToBanner);
    }       
}
