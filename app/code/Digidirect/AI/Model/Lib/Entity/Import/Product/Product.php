<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\Product;

use Digidirect\AI\Model\Lib\Entity\Import\ImportAbstract;
use Digidirect\AI\Model\Lib\Entity\Import\Service\Source\ArraySource;
use Digidirect\AI\Model\Lib\Entity\Import\Service\Source\ArraySourceFactory;
use Digidirect\AI\Model\Lib\Entity\Import\Service\ErrorProcessing\Product\ProcessingErrorAggregator;
use Digidirect\AI\Model\Lib\Validator\Validate as Validator;
use Digidirect\AI\Model\Logger\LoggerInterface;
use Magento\ImportExport\Model\Import as MagentoImport;
use Magento\CatalogImportExport\Model\Import\Product as ProductImport;
use Magento\CatalogImportExport\Model\Import\ProductFactory as MagentoProductImport;
use Magento\Framework\Data\DataArray;

class Product extends ImportAbstract implements ProductInterface
{
    const ENTITY_NAME = 'product';

    /**
     * @var ProductImport
     */
    protected $productImport;

    /**
     * @var ArraySourceFactory
     */
    protected $arraySourceFactory;

    /**
     * @var ProcessingErrorAggregator
     */
    protected $errorAggregator;

    /**
     * @var array
     */
    protected $colNames;

    /**
     * @var array
     */
    protected $importParameters = [];

    /**
     * ImportAbstract constructor.
     * @param Validator $beforeSaveValidator
     * @param Validator $beforeUpdateValidator
     * @param LoggerInterface $logger
     * @param ArraySourceFactory $arraySourceFactory
     * @param ProcessingErrorAggregator $errorAggregator
     * @param MagentoProductImport $productImportFactory
     * @param DataArray $colNames
     * @param array $preparers
     */
    public function __construct(
        Validator $beforeSaveValidator,
        Validator $beforeUpdateValidator,
        LoggerInterface $logger,
        ArraySourceFactory $arraySourceFactory,
        ProcessingErrorAggregator $errorAggregator,
        MagentoProductImport $productImportFactory,
        DataArray $colNames,
        array $preparers = []
    ) {
        parent::__construct(
            $beforeSaveValidator,
            $beforeUpdateValidator,
            $logger,
            $preparers
        );

        $this->arraySourceFactory = $arraySourceFactory;
        $this->errorAggregator = $errorAggregator;
        $this->errorAggregator->setLogger($logger);
        $this->productImport = $productImportFactory->create([
            'errorAggregator' => $this->errorAggregator
        ]);
        $this->colNames = array_keys($colNames->getData());
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::ENTITY_NAME;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setParameter($name, $value)
    {
        $this->importParameters[$name] = $value;
        return $this;
    }

    /**
     * @param array $product
     * @param bool $updateOnDuplicate
     * @return bool
     */
    protected function _save(array $product, $updateOnDuplicate = true)
    {
        /**
         * @var $arraySource ArraySource
         */
        $arraySource = $this->arraySourceFactory->create(['colNames' => $this->colNames]);
        $arraySource->addEntity($product);
        $behavior = $updateOnDuplicate ? MagentoImport::BEHAVIOR_ADD_UPDATE : MagentoImport::BEHAVIOR_APPEND;
        return $this->_importBunch($arraySource, $behavior);
    }

    /**
     * @param array $products
     * @param bool $updateOnDuplicate
     * @return bool
     */
    protected function _saveBunch(array $products, $updateOnDuplicate = true)
    {
        $arraySource = $this->arraySourceFactory->create([
            'colNames' => $this->colNames,
            'data' => $products,
        ]);
        $behavior = $updateOnDuplicate ? MagentoImport::BEHAVIOR_ADD_UPDATE : MagentoImport::BEHAVIOR_APPEND;
        return $this->_importBunch($arraySource, $behavior);
    }

    /**
     * @param array $product
     * @return bool
     */
    protected function _update(array $product)
    {
        $arraySource = $this->arraySourceFactory->create(['colNames' => $this->colNames]);
        $arraySource->addEntity($product);
        return $this->_importBunch($arraySource, MagentoImport::BEHAVIOR_REPLACE);
    }

    /**
     * @param array $products
     * @return bool
     */
    protected function _updateBunch(array $products)
    {
        $arraySource = $this->arraySourceFactory->create([
            'colNames' => $this->colNames,
            'data' => $products,
        ]);
        return $this->_importBunch($arraySource, MagentoImport::BEHAVIOR_REPLACE);
    }

    /**
     * @param string|array $sku
     * @return bool
     */
    public function delete($sku)
    {
        $data = [];
        if (!is_array($sku)) {
            $sku = [$sku];
        }

        foreach ($sku as $value) {
            $data[] = [
                ProductImport::COL_SKU => $value
            ];
        }

        $arraySource = $this->arraySourceFactory->create([
            'colNames' => [ProductImport::COL_SKU],
            'data' => $data,
        ]);

        return $this->_importBunch($arraySource, MagentoImport::BEHAVIOR_DELETE);
    }

    /**
     * @param ArraySource $arraySource
     * @param string $behavior
     * @return bool
     */
    protected function _importBunch(ArraySource $arraySource, $behavior)
    {
        $this->errorAggregator->clear();
        $this->setParameter('behavior', $behavior);
        $this->productImport->setParameters($this->importParameters);
        $this->productImport->setSource($arraySource);
        $this->productImport->getErrorAggregator()->setProductItems($arraySource->getData());
        $this->productImport->validateData();
        if ($this->errorAggregator->getErrorsCount() > 0) {
            $this->_logger->critical(__('Data is not valid.'), [
                'entity' => $this->getName(),
                'behavior' => $behavior,
                'errors' => $this->errorAggregator->getErrorMessages(),
                'data' => $arraySource->getData()
            ]);
        }
        $this->productImport->importData();
        return true;
    }
}
