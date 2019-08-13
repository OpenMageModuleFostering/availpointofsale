<?php
/**
 * Avail - Point of Sale
 *
 * @category  Avail
 * @package   Avail_Pointofsale
 * @copyright 2012 Avail
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class Avail_Pointofsale_Block_Select_Attributes extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Grid columns
     *
     * @var array
     */
    protected $_columns = array();

    /**
     * Enable the "Add after" button or not
     *
     * @var bool
    */
    protected $_addAfter = true;

    /**
     * Label of add button
     *
     * @var unknown_type
     */
    protected $_addButtonLabel;

    /**
     * Rows cache
     *
     * @var array|null
     */
    private $_arrayRowsCache;

    /**
     * Indication whether block is prepared to render or no
     *
     * @var bool
     */
    protected $_isPreparedToRender = false;

    /**
     * Attributes array
     *
     * @var array
     */
    protected $_attributes = array();
    
    /**
     * stores array
     * @var array
    */
    protected $_stores = array();

    /**
     * (uasort) Used to sort an array by using the label key.
     * @param string $a
     * @param string $b
     * @return int
     */
    private function cmpSortArray($a, $b)
    {
        if ($a['label'] == $b['label']) {
            return 0;
        }
        
        return ($a['label'] < $b['label']) ? -1 : 1;
    }
    
    public function __construct()
    {
        if (!$this->_addButtonLabel) {
            $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add');
        }
        
        parent::__construct();
        
        if (!$this->getTemplate()) {
            $this->setTemplate('pointofsale/select/attributes/array.phtml');
        }
        // get the data model for the product
        $model = Mage::getResourceModel('catalog/product');
        // get the entity type
        $typeId = $model->getTypeId();
        // get all the attribute of the product
        $attrs = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter($typeId)
            ->load();

        // construct the attributes elements and put them in an array
        // Customized attribute
        $this->_attributes[] = array(
            'value' => 'stock',
            'label' => 'Stock'
        );

        foreach ($attrs as $att) {
            $this->_attributes[] = array(
                'value' => $att->attribute_code,
                'label' => ucfirst(str_replace('\'', '', trim(Mage::helper('catalog')->__($att->getFrontend()->getLabel()))))
            );
        }

        //Update the array sort
        uasort($this->_attributes, array($this, 'cmpSortArray'));

        //extract stores
        $stores = Mage::app()->getStores(false, true);
        //to add the default store
        $this->_stores[] = array(
            'value' => 0,
            'label' => 'default'
        );
        
        foreach ($stores as $st) {
            $this->_stores[] = array(
                'value'	=> $st->getStoreId(),
                'label'	=> $st->getName()
            );
        }
    }

    /**
     * Add a column to array-grid
     *
     * @param string $name
     * @param array $params
     */
    public function addColumn($name, $params)
    {
        $this->_columns[$name] = array(
            'label' => empty($params['label']) ? 'Column' : $params['label'],
            'size' => empty($params['size']) ? false : $params['size'],
            'style' => empty($params['style']) ? null : $params['style'],
            'class' => empty($params['class']) ? null : $params['class'],
            'renderer' => false,
        );
        
        if ((!empty($params['renderer'])) && ($params['renderer'] instanceof Mage_Core_Block_Abstract)) {
            $this->_columns[$name]['renderer'] = $params['renderer'];
        }
    }

    /**
     * Get the grid and scripts contents
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $html = $this->_toHtml();
        $this->_arrayRowsCache = null; // doh, the object is used as singleton!
        return $html;
    }

    /**
     * Prepare existing row data object
     *
     * @param Varien_Object
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        // override in descendants
    }

    /**
     * Obtain existing data from form element
     *
     * Each row will be instance of Varien_Object
     *
     * @return array
     */
    public function getArrayRows()
    {
        if (null !== $this->_arrayRowsCache) {
            return $this->_arrayRowsCache;
        }
        $result = array();
        /** @var Varien_Data_Form_Element_Abstract */
        $element = $this->getElement();

        if ($element->getValue() && is_array($element->getValue())) {
            foreach ($element->getValue() as $rowId => $row) {
                foreach ($row as $key => $value) {
                    $row[$key] = $this->htmlEscape($value);
                }
                $row['_id'] = $rowId;
                $result[$rowId] = new Varien_Object($row);
                $this->_prepareArrayRow($result[$rowId]);
            }
        }
        $this->_arrayRowsCache = $result;
        
        return $this->_arrayRowsCache;
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     */
    protected function _renderCellTemplate($columnName)
    {
        if ($columnName == 'stores') {
            $options = $this->_stores;
        } elseif ($columnName == 'attributes') {
            $options = $this->_attributes;
        }
        
        if (empty($this->_columns[$columnName])) {
            throw new Exception('Wrong column name specified.');
        }
        
        $column     = $this->_columns[$columnName];
        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';

        if ($column['renderer']) {
            return $column['renderer']->setInputName($inputName)->setColumnName($columnName)->setColumn($column)
                ->toHtml();
        }

        $result = '<select type="text" name="' . $inputName . '" value="#{' . $columnName . '}" ' .
                ($column['size'] ? 'size="' . $column['size'] . '"' : '') . ' class="' .
                (isset($column['class']) ? $column['class'] : 'input-text') . '"'.
                (isset($column['style']) ? ' style="'.$column['style'] . '"' : '') . '>';
        
        foreach ($options as $op) {
            $result .= '<option value="'.$op['value'].'" >'.$op['label'].'</option>';
        }
        
        $result .='</select>';
        
        return $result;
    }

    /**
     * Prepare to render
     */
    protected function _prepareToRender()
    {
        // Override in descendants to add columns, change add button label etc
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_isPreparedToRender) {
            $this->_prepareToRender();
            $this->_isPreparedToRender = true;
        }
        
        if (empty($this->_columns)) {
            throw new Exception('At least one column must be defined.');
        }
        
        return parent::_toHtml();
    }
}
