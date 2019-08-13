<?php
/**
 * Avail - Point of Sale
 *
 * @category  Avail
 * @package   Avail_Pointofsale
 * @copyright 2013 Avail
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Avail_Pointofsale_Block_Config_Form_Field_Button extends Avail_Pointofsale_Block_Select_Attributes
{
    /**
     * Internal constructor
     */
    public function __construct()
    {
        $this->addColumn('attributes', array(
                'label' => Mage::helper('pointofsale')->__('Attributes'),
                'style' => 'width:120px',
        ));
        
        $this->addColumn('stores', array(
                'label' => Mage::helper('pointofsale')->__('Stores'),
                'style' => 'width:120px',
        ));
        
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('pointofsale')->__('Add Assoctiation');
        
        parent::__construct();
    }
}
