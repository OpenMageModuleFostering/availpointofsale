<?php
/**
 * Avail - Point of Sale
 *
 * @category  Avail
 * @package   Avail_Pointofsale
 * @copyright 2013 Avail
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Avail_Pointofsale_GenerateController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Create the configuration files and display the response message .
     *
     * @return bool
     */
    public function indexAction()
    {
        $modelInstaller = Mage::getSingleton('pointofsale/installer');

        if ($modelInstaller->verifyConf()) {
            $modelInstaller->createFiles();
            Mage::getSingleton('core/session')->addSuccess('File has been created successfully !');
        } else {
            Mage::getSingleton('core/session')->addError('File has not been created , please check your configuration !');
        }
        
        $this->_redirect("adminhtml/system_config/edit/section/pointofsale/");
    }
}
