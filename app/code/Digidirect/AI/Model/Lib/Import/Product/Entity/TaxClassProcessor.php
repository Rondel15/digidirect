<?php
namespace Digidirect\AI\Model\Lib\Import\Product\Entity;

use Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor as DefaultTaxClassProcessor;
use Magento\CatalogImportExport\Model\Import\Product\Type\AbstractType;
use Magento\Tax\Model\ClassModel;

class TaxClassProcessor extends DefaultTaxClassProcessor
{
    /**
     * Connection
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->_connection = $resource->getConnection();
        $this->initTaxClasses();
    }

    /**
     * Init tax classes
     *
     * @return $this
     */
    protected function initTaxClasses()
    {
        if (empty($this->taxClasses)) {
            $select = $this->_connection->select()
                ->from($this->_connection->getTableName('tax_class'), ['class_id', 'class_name'])
                ->where('class_type = ?', ClassModel::TAX_CLASS_TYPE_PRODUCT);

            $taxClasses = $this->_connection->fetchAll($select);
            foreach ($taxClasses as $taxClass) {
                $this->taxClasses[$taxClass['class_name']] = $taxClass['class_id'];
            }
        }
        return $this;
    }

    /**
     * Creates new tax class.
     *
     * @param string $taxClassName
     * @param AbstractType $productTypeModel
     * @return integer
     */
    protected function createTaxClass($taxClassName, AbstractType $productTypeModel)
    {
        $tableName = $this->_connection->getTableName('tax_class');
        $this->_connection->insert(
            $tableName,
            ['class_name' => $taxClassName, 'class_type' => ClassModel::TAX_CLASS_TYPE_PRODUCT]
        );

        $newTaxClass = $this->_connection->fetchOne($this->_connection->select()->from(
            $tableName,
            ['class_id']
        )->where(
            'class_name = ?',
            $taxClassName
        )->where(
            'class_type = ?',
            ClassModel::TAX_CLASS_TYPE_PRODUCT
        ));

        $id = $newTaxClass;

        $productTypeModel->addAttributeOption(self::ATRR_CODE, $id, $id);

        return $id;
    }
}
