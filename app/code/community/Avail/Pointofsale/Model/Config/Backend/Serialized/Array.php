<?php
/**
 * Avail - Point of Sale
 *
 * @category  Avail
 * @package   Avail_Pointofsale
 * @copyright 2013 Avail
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backend for serialized array data
 *
 */
class Avail_Pointofsale_Model_Config_Backend_Serialized_Array extends Mage_Adminhtml_Model_System_Config_Backend_Serialized
{
    /**
     * Unset array element with '__empty' key
     *
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();

        if (is_array($value)) {
            unset($value['__empty']);
        }
        
        $this->setValue($value);
        parent::_beforeSave();
    }
}
