<?php
/**
 * Avail - Point of Sale
 *
 * @category  Avail
 * @package   Avail_Pointofsale
 * @copyright 2013 Avail
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Avail_Pointofsale_Block_Data_Banner_Category extends Avail_Pointofsale_Block_Data_Banner
{
    /**
     * Path to the banner name in sys configuration
     * 
     * @var string
     */
    protected $_xmlPathToBanner = 'pointofsale/template/category';
    
    /**
     * store the given category
     */
    protected $_category;

    /**
     * Internal constructor
     */
    protected function _construct()
    {
        $this->_category = Mage::registry('current_category');
        parent::_construct();
    }

    /**
     * Retrieve the dynamic parameter for the avail banner
     *
     * @return string String
     */
    protected function _getDynamicParamter()
    {
        $dynParam = '';
        $categoryId = $this->_getCategoryId();

        if (!is_null($categoryId)) {
            $dynParam = 'append orcat in subtemplate ALL with ' . $categoryId;
        }

        return $dynParam;
    }
    
    /**
     * Retrieve the current category id either from the registry or from filters
     * 
     * @return int
     */
    protected function _getCategoryId()
    {
        $categoryId = null;
        $categoryFilter = $this->getRequest()->getParam('cat');
        
        if (is_numeric($categoryFilter)) {
            $categoryId = $categoryFilter;
        } elseif ($this->_category && $this->_category->getId()) {
            $categoryId = $this->_category->getId();
        }
        
        return $categoryId;
    }

    /**
     * Return the Avail category banner
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = '';
        $bannerName = $this->getBannerName();
        $dynParam = $this->_getDynamicParamter();
        
        if (!empty($bannerName)) {
            $html = '<div data-id="avail" data-banner="' . $bannerName . '" data-dynpara="' . $dynParam . '"></div>';
        }
        
        return $html;
    }
}
