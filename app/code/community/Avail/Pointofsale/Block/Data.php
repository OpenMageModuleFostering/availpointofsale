<?php
/**
 * Avail - Point of Sale
 *
 * @category  Avail
 * @package   Avail_Pointofsale
 * @copyright 2013 Avail
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Avail_Pointofsale_Block_Data extends Mage_Core_Block_Template
{
    /**
     * Return if the debug mode is allowed or not
     *
     * @return bool
     */
    public function isDebugMode()
    {
        return Mage::helper('pointofsale')->isDebugMode();
    }
    
    /**
     * Return if there's a default configuration
     *
     * @return bool
     */
    public function isConfigured() 
    {
        return Mage::helper('pointofsale')->isConfigured();
    }
}
