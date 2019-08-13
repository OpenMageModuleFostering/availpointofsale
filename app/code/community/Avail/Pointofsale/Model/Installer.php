<?php
/**
 * Avail - Point of Sale
 *
 * @category  Avail
 * @package   Avail_Pointofsale
 * @copyright 2013 Avail
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Avail_Pointofsale_Model_Installer {

    /**
     * Define the configuration file name
     * @var string
     */
    const  FILENAME = 'upload.conf';
    
    /**
     * Define the admin store id
     * @var string
     */
    const ADMIN_STORE_ID = Mage_Core_Model_App::ADMIN_STORE_ID;
    
    /**
     * Store the file data
     * @var array
     */
    private $fileData;
    
    /**
     * Store the file connection data
     * @var string
     */
    private $fileConnectionData;

    /**
     * Verification of the data configuration
     * 
     * @return boolean
     */
    public function verifyConf()
    {
        $confVars = array (Mage::getStoreConfig('pointofsale/account/customer_id'),
                Mage::getStoreConfig('pointofsale/account/password'),
                Mage::getStoreConfig('pointofsale/interface/attribute'),
                Mage::getStoreConfig('pointofsale/template/default'),
                Mage::getStoreConfig('pointofsale/template/search'),
                Mage::getStoreConfig('pointofsale/template/product'),
                Mage::getStoreConfig('pointofsale/template/category'),
                Mage::getStoreConfig('pointofsale/template/cart')
        );
        
        foreach ($confVars as $cv) {
            if (empty($cv)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Create config files and cron schedules
     *
     */
    public function createFiles()
    {
        //create the init config file
        $this->_initFile(true);
        //create the ordinary config file
        $this->_initFile();
    }

    /**
     * Initiate files contents (update.conf & update.conf.init)
     *
     */
    protected function _initFile($init = false)
    {
        $this->fileData	= array();

        if ($init) {
            $fileName = self::FILENAME;
        } else {
            $fileName = self::FILENAME.'.after';
        }

        //extract stores
        $stores = Mage::app()->getStores(false, true);

        foreach ($stores as $st) {
            $this->_stores[] = array(
                'value' => $st->getStoreId(),
                'label' => $st->getName()
            );
        }
        
        $baseUrl	= Mage::getBaseUrl();
        $storeUrl	= Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $mediaUrl	= Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product/';

        //Customer authentification
        $customerId = Mage::getStoreConfig('pointofsale/account/customer_id');
        $customerPassword = Mage::getStoreConfig('pointofsale/account/password');
        $optionalAttrs = Mage::getStoreConfig('pointofsale/interface/attribute');
        $configFlagPath = 'pointofsale/flag/sql';
        $optionalAttrs = unserialize($optionalAttrs);

        $availDataExporterPath = Mage::getBaseDir() . DS . 'availdataexporter';
        $availDefaulConfigData = 'default/pointofsale';
        $configData = Mage::getConfig()->getNode($availDefaulConfigData);

        $connectionConfig = Mage::getConfig()->getResourceConnectionConfig('default_setup');
        $mysqlHost = $connectionConfig->host;
        $mysqlDb = $connectionConfig->dbname;
        $mysqlUser = $connectionConfig->username;
        $mysqlPassword = $connectionConfig->password;

        $installer = Mage::getSingleton('core/resource');
        #sql tables
        $categoryProductTable = $installer->getTableName('catalog/category_product_index');
        $cceVarcharTable = $installer->getTableName('catalog_category_entity_varchar');
        $eavAttributeTable = $installer->getTableName('eav_attribute');
        $cpeTable = $installer->getTableName('catalog_product_entity');
        $cisiTable = $installer->getTableName('cataloginventory_stock_item');
        $cpeIntTable = $installer->getTableName('catalog_product_entity_int');
        $cpeVarcharTable = $installer->getTableName('catalog_product_entity_varchar');
        $cpeDecimalTable = $installer->getTableName('catalog_product_entity_decimal');
        $cpeDatetimeTable = $installer->getTableName('catalog_product_entity_datetime');
        $cpeTextTable = $installer->getTableName('catalog_product_entity_text');
        $soitemTable = $installer->getTableName('sales/order_item');
        $configTable = $installer->getTableName('core_config_data');
        $soTable = $installer->getTableName('sales/order');
        $stockTable = $installer->getTableName('cataloginventory_stock_item');
        $sellingPriceTable = $installer->getTableName('catalog_product_index_price');

        // the type of attributes
        // automatic attributes
        $sqlJoins = null;
        $sqlSelects = null;
        $counter = 1;
        $haystack = array('stock', 'selling_price');
        
        foreach ($optionalAttrs as  $op) {
            $opAttribute=$op['attributes'];
            $opStore=$op['stores'];
            $storeName = strtolower(Mage::app()->getStore($opStore)->getName());
            if ($counter == 1) {
                $sqlSelects = ',';
            }
            if (in_array($opAttribute, $haystack)) {
                $tableName = '`_table_'.$opAttribute.'_'.$storeName.'`';
                if ($opAttribute == 'stock') {
                    if($counter != count($optionalAttrs)) {
                        $sqlSelects .= 'IFNULL(CONVERT('.$tableName.'.`qty`, UNSIGNED INTEGER) ,\'\') as '.$opAttribute.'_'.$storeName.',';
                    } else {
                        $sqlSelects .= 'IFNULL(CONVERT('.$tableName.'.`qty`, UNSIGNED INTEGER) ,\'\') as '.$opAttribute.'_'.$storeName;
                    }
                    $sqlJoins .=" LEFT JOIN `{$stockTable}` AS {$tableName} ON ({$tableName}.product_id = e.entity_id) ";
                }
            } else {
                $attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product',$opAttribute);
                $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
                $tableName = '`_table_'.$opAttribute.'_'.$storeName.'`';
                
                if($counter != count($optionalAttrs)) {
                    $sqlSelects .= 'IFNULL('.$tableName.'.`value`,\'\') as '.$opAttribute.'_'.$storeName.',';
                } else {
                    $sqlSelects .= 'IFNULL('.$tableName.'.`value`,\'\') as '.$opAttribute.'_'.$storeName;
                }
                
                $counter++;
                
                switch ($attribute->getbackendType()) {
                    case 'int':
                        $sqlJoins .=" LEFT JOIN `{$cpeIntTable}` AS {$tableName} ON ({$tableName}.entity_id = e.entity_id) AND ({$tableName}.attribute_id='{$attribute->getAttributeId()}') AND ({$tableName}.store_id={$opStore}) ";
                        break;
                    case 'varchar':
                        $sqlJoins .=" LEFT JOIN `{$cpeVarcharTable}` AS {$tableName} ON ({$tableName}.entity_id = e.entity_id) AND ({$tableName}.attribute_id='{$attribute->getAttributeId()}') AND ({$tableName}.store_id={$opStore}) ";
                        break;
                    case 'decimal':
                        $sqlJoins .=" LEFT JOIN `{$cpeDecimalTable}` AS {$tableName} ON ({$tableName}.entity_id = e.entity_id) AND ({$tableName}.attribute_id='{$attribute->getAttributeId()}') AND ({$tableName}.store_id={$opStore}) ";
                        break;
                    case 'text':
                        $sqlJoins .=" LEFT JOIN `{$cpeTextTable}` AS {$tableName} ON ({$tableName}.entity_id = e.entity_id) AND ({$tableName}.attribute_id='{$attribute->getAttributeId()}') AND ({$tableName}.store_id={$opStore}) ";
                        break;
                    default:
                        $sqlJoins .=" LEFT JOIN `{$cpeDatetimeTable}` AS {$tableName} ON ({$tableName}.entity_id = e.entity_id) AND ({$tableName}.attribute_id='{$attribute->getAttributeId()}') AND ({$tableName}.store_id={$opStore}) ";
                        break;
                        break;
                }
            }
        }
        
        $installer = Mage::getModel('eav/config');
        $attributeModel = Mage::getResourceModel('eav/entity_attribute');
        $categoryEntityType = $installer->getEntityType('catalog_category');
        $categoryEntityTypeId =(int) $categoryEntityType->getEntityTypeId();
        $productEntityType = $installer->getEntityType('catalog_product');
        $productEntityTypeId =(int) $productEntityType->getEntityTypeId();
        $statusAttributeId = $attributeModel->getIdByCode('catalog_product', 'status');
        $visibilityAttributeId = $attributeModel->getIdByCode('catalog_product', 'visibility');
        $priceAttributeId = $attributeModel->getIdByCode('catalog_product', 'price');
        $nameAttributeId = $attributeModel->getIdByCode('catalog_product', 'name');
        $urlAttributeId = $attributeModel->getIdByCode('catalog_product', 'url_path');
        $imageAttributeId = $attributeModel->getIdByCode('catalog_product', 'image');

        #connection string & driver
        $this->fileConnectionData = implode("\n", array(
        'SOURCE_SQL_CONNECTIONSTRING="jdbc:mysql://' . $mysqlHost . '/' . $mysqlDb . '?user=' . $mysqlUser . '&password=' . $mysqlPassword . '"',
        'SOURCE_SQL_DRIVER="com.mysql.jdbc.Driver"')
        );

        // File construction
        $this->_getCustomerData($customerId,$customerPassword);
        $this->_getCategoryData($categoryProductTable);
        $this->_getCategoryNameData($cceVarcharTable, $eavAttributeTable, $categoryEntityTypeId);
        $this->_getValidData($cpeTable, $cisiTable, $cpeIntTable,
                $cpeIntTable, $visibilityAttributeId, $cpeIntTable,
                $statusAttributeId);
        $this->_getProductData($storeUrl,$mediaUrl, $sqlSelects,
                $cpeTable, $sqlJoins, $cpeIntTable,
                $visibilityAttributeId, $sellingPriceTable,
                $cpeDecimalTable, $priceAttributeId,
                $cpeVarcharTable, $urlAttributeId, $nameAttributeId,
                $imageAttributeId);

        if ($init) {
            $this->_getTransactionData($soitemTable,$soTable);
        }

        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $availDataExporterPath));

        if ($io->fileExists($fileName) && !$io->isWriteable($fileName)) {
            Mage::throwException(Mage::helper('avail')->__('File "%s" cannot be saved. Please, make sure the directory "%s" is writeable by web server.', $fileName, $availDataExporterPath));
        }

        $io->streamOpen($fileName);
        $io->streamWrite(implode("\n", $this->fileData));
        $io->streamClose();
    }

    /**
     * Prepare the customer data
     */
    protected function _getCustomerData($customerId,$customerPassword)
    {
        #customer_id/password
        $this->fileData[] = '#Retrieve Customer Id and Password from default config data';
        $this->fileData[] = 'CUSTOMER_ID="' . $customerId . '"';
        $this->fileData[] = 'PASSWORD="' . $customerPassword . '"';
        $this->fileData[] = '';
    }

    /**
     * Prepare the category data
     */
    protected function _getCategoryData($categoryProductTable)
    {
        #categorydata(ProductId, CategoryId)
        $categoryDataSql = "SELECT product_id AS ProductId, category_id AS CategoryId FROM {$categoryProductTable} ORDER BY product_id";
        $this->fileData[] = '#categorydata';
        $this->fileData[] = '{';
        $this->fileData[] = 'DATA_TYPE="categorydata"';
        $this->fileData[] = $this->fileConnectionData;
        $this->fileData[] = 'SOURCE_SQL_QUERY="' . $categoryDataSql . '"';
        $this->fileData[] = '}';
        $this->fileData[] = '';
    }

    /**
     * Prepare the category name data
     */
    protected function _getCategoryNameData($cceVarcharTable, $eavAttributeTable, $categoryEntityTypeId)
    {
        #categorynamesdata(CategoryId, Category Name)
        $categoryNamesDataSql = "SELECT entity_id AS CategoryId, CONCAT(entity_id, ' ', value) AS `Category Name` from {$cceVarcharTable} where attribute_id = (select attribute_id from {$eavAttributeTable} where attribute_code = 'name' and entity_type_id = {$categoryEntityTypeId}) and store_id = ".self::ADMIN_STORE_ID ;
        $this->fileData[] = '#categorynamesdata';
        $this->fileData[] = '{';
        $this->fileData[] = 'DATA_TYPE="categorynamesdata"';
        $this->fileData[] = $this->fileConnectionData;
        $this->fileData[] = 'SOURCE_SQL_QUERY="' . $categoryNamesDataSql . '"';
        $this->fileData[] = '}';
        $this->fileData[] = '';
    }

    /**
     * Prepare the valid data
     */
    protected function _getValidData($cpeTable, $cisiTable, $cpeIntTable,
            $cpeIntTable, $visibilityAttributeId, $cpeIntTable,
            $statusAttributeId)
    {
        #validdata(ProductId)
        $validDataSql = "SELECT `e`.`entity_id` AS ProductId FROM `{$cpeTable}` AS `e` LEFT JOIN `{$cisiTable}` AS `_table_qty` ON (_table_qty.product_id=e.entity_id) AND (_table_qty.stock_id=1) INNER JOIN `{$cpeIntTable}` AS `_table_visibility` ON (_table_visibility.entity_id = e.entity_id) AND (_table_visibility.attribute_id='{$visibilityAttributeId}') AND (_table_visibility.store_id=".self::ADMIN_STORE_ID.") INNER JOIN `{$cpeIntTable}` AS `_table_status` ON (_table_status.entity_id = e.entity_id) AND (_table_status.attribute_id='{$statusAttributeId}') AND (_table_status.store_id=0) WHERE (_table_visibility.value IN(2, 4)) AND (_table_status.value = '1') ORDER BY `e`.`entity_id`";
        $this->fileData[] = '#validdata';
        $this->fileData[] = '{';
        $this->fileData[] = 'DATA_TYPE="validdata"';
        $this->fileData[] = $this->fileConnectionData;
        $this->fileData[] = 'SOURCE_SQL_QUERY="' . $validDataSql . '"';
        $this->fileData[] = '}';
        $this->fileData[] = '';
    }

    /**
     * Prepare the product data
     */
    protected function 	_getProductData($storeUrl,$mediaUrl, $sqlSelects,
            $cpeTable, $sqlJoins, $cpeIntTable,
            $visibilityAttributeId, $sellingPriceTable,
            $cpeDecimalTable, $priceAttributeId,
            $cpeVarcharTable, $urlAttributeId, $nameAttributeId,
            $imageAttributeId)
    {
        #$sqlSelects containts the optional attributes
        #productdata(ProductId, Name, Price, Image, Url)
        $productDataSql = "SELECT  `e`.`entity_id` AS `ProductId`, `_table_name`.`value` AS `ProductName`, IFNULL(`_table_sellingprice`.`final_price`,'') AS `SellingPrice`, `_table_price`.`value` AS `Price`, CONCAT('{$storeUrl}', `_table_url`.`value`) AS `ProductPageURL`, CONCAT('{$mediaUrl}',`_table_image`.`value`) AS `ImageURL` {$sqlSelects} FROM `{$cpeTable}` AS `e` {$sqlJoins} INNER JOIN `{$cpeIntTable}` AS `_table_visibility` ON (_table_visibility.entity_id = e.entity_id) AND (_table_visibility.attribute_id='{$visibilityAttributeId}') AND (_table_visibility.store_id=0) INNER JOIN `{$sellingPriceTable}` AS _table_sellingprice ON (_table_sellingprice.entity_id = e.entity_id) AND (customer_group_id = 1) INNER JOIN `{$cpeDecimalTable}` AS `_table_price` ON (_table_price.entity_id = e.entity_id) AND (_table_price.attribute_id='{$priceAttributeId}') AND (_table_price.store_id=0) INNER JOIN `{$cpeVarcharTable}` AS `_table_name` ON (_table_name.entity_id = e.entity_id) AND (_table_name.attribute_id='{$nameAttributeId}') AND (_table_name.store_id=0) INNER JOIN `{$cpeVarcharTable}` AS `_table_url` ON (_table_url.entity_id = e.entity_id) AND (_table_url.attribute_id='{$urlAttributeId}') AND (_table_url.store_id=0) INNER JOIN `{$cpeVarcharTable}` AS `_table_image` ON (_table_image.entity_id = e.entity_id) AND (_table_image.attribute_id='{$imageAttributeId}') AND (_table_image.store_id=0) WHERE (_table_visibility.value IN(2, 4)) ORDER BY `e`.`entity_id`";
        $this->fileData[] = '#productdata';
        $this->fileData[] = '{';
        $this->fileData[] = 'DATA_TYPE="productdata"';
        $this->fileData[] = $this->fileConnectionData;
        $this->fileData[] = 'SOURCE_SQL_QUERY="' . $productDataSql . '"';
        $this->fileData[] = '}';
        $this->fileData[] = '';
    }

    /**
     * Prepare the transaction data
     */
    protected function 	_getTransactionData($soitemTable,$soTable)
    {
        #transactiondata(UserId, ProductId, OrderId)
        $transactionDataSql = "SELECT `order`.`customer_id`, `main_table`.`product_id`, `order`.`increment_id` FROM `{$soitemTable}` AS `main_table`  INNER JOIN `{$soTable}` AS `order` ON `order`.`entity_id` = `main_table`.`order_id`;";
        $this->fileData[] = '#transactiondata';
        $this->fileData[] = '{';
        $this->fileData[] = 'DATA_TYPE="transactiondata"';
        $this->fileData[] = $this->fileConnectionData;
        $this->fileData[] = 'SOURCE_SQL_QUERY="' . $transactionDataSql . '"';
        $this->fileData[] = '}';
        $this->fileData[] = '';
    }
}
