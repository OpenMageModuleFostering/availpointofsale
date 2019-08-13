<?php
/**
 * Avail - Point of Sale
 *
 * @category  Avail
 * @package   Avail_Pointofsale
 * @copyright 2013 Avail
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Avail_Pointofsale_Block_Head extends Mage_Core_Block_Text
{
    /**
     * Add the JS call to the head of all pages .
     *
     * @return string
     */
    public function addJs($name)
    {
        $js = '';
        
        if ($this->helper('pointofsale')->isConfigured()) {
            $script = '<script type="text/javascript" src="%s"></script>';
            $protocol = $this->helper('pointofsale')->getProtocol();
            $url = Mage::getStoreConfig('pointofsale/interface/url');
            $url = (substr($url, -1) === '/') ? $url : $url . '/';
            $url = str_replace('http', $protocol, $url);
            $url .= Mage::getStoreConfig('pointofsale/account/customer_id') . '/' . $name.'?decorate=1';
            $js = sprintf($script, $url);
        }
        
        $this->setText($js);
        return $this;
    }

}