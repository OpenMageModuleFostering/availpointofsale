<?php
/**
 * Avail - Point of Sale
 *
 * @category  Avail
 * @package   Avail_Pointofsale
 * @copyright 2013 Avail
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Avail_Pointofsale_Block_Config_Form_Field_GenerateButton extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Return the scalable button for generating the configuration files
     *
     * @return string $html
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $url = $url = $this->getUrl('pointofsale/generate/index');

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel('Generate Now !')
                    ->setOnClick("setLocation('$url')")
                    ->toHtml();

        return $html;
    }
}
