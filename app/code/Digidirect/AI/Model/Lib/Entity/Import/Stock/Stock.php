<?php

namespace Digidirect\AI\Model\Lib\Entity\Import\Stock;

use Digidirect\AI\Model\Lib\Entity\Import\ImportAbstract;
use Digidirect\AI\Model\Lib\Entity\Import\Service\Source\ArraySource;
use Digidirect\AI\Model\Lib\Validator\Validate as Validator;
use Digidirect\AI\Model\Logger\LoggerInterface;
use Digidirect\AI\Model\Lib\Entity\Import\Stock\Processor\Data;
use Digidirect\AI\Model\Lib\Entity\Import\Stock\Processor\DataFactory;
use Magento\Framework\Data\DataArray;
use Digidirect\AI\Model\Lib\Entity\Import\Service\ErrorProcessing\ProcessingErrorAggregator;
use Digidirect\AI\Model\Lib\Entity\Import\Service\Source\ArraySourceFactory;
use Magento\ImportExport\Model\Import as MagentoImport;

class Stock extends ImportAbstract implements StockInterface
{
    const NAME = 'stock';

    /**
     * @var DataFactory
     */
    protected $importProcessorFactory;

    /**
     * @var Data
     */
    protected $importProcessor;

    /**
     * @var ProcessingErrorAggregator
     */
    protected $errorAggregator;

    /**
     * @var ArraySourceFactory
     */
    protected $arraySourceFactory;

    /**
     * @var array
     */
    protected $colNames;

    /**
     * @var array
     */
    protected $importParameters = [];

    /**
     * Stock constructor.
     * @param Validator $beforeSaveValidator
     * @param Validator $beforeUpdateValidator
     * @param LoggerInterface $logger
     * @param DataFactory $processor
     * @param DataArray $dataArray
     * @param ProcessingErrorAggregator $processingErrorAggregator
     * @param ArraySourceFactory $arraySourceFactory
     * @param array $preparers
     */
    public function __construct(
        Validator $beforeSaveValidator,
        Validator $beforeUpdateValidator,
        LoggerInterface $logger,
        DataFactory $processor,
        DataArray $colNames,
        ProcessingErrorAggregator $processingErrorAggregator,
        ArraySourceFactory $arraySourceFactory,
        array $preparers = []
    ) {
        parent::__construct($beforeSaveValidator, $beforeUpdateValidator, $logger, $preparers);
        $this->importProcessorFactory = $processor;
        $this->errorAggregator = $processingErrorAggregator;
        $this->arraySourceFactory = $arraySourceFactory;
        $this->errorAggregator->setLogger($logger);
        $this->colNames = array_keys($colNames->getData());
    }

    /**
     * As it doesn't make sense to save without product_id/sku - we use only replace behaviour
     *
     * @param array $entity
     * @param bool $updateOnDuplicate
     * @return bool
     */
    protected function _save(array $entity, $updateOnDuplicate = true)
    {
        return $this->update($entity);
    }

    /**
     * As it doesn't make sense to save without product_id/sku - we use only replace behaviour
     *
     * @param array $entities
     * @param bool $updateOnDuplicate
     * @return bool
     */
    protected function _saveBunch(array $entities, $updateOnDuplicate = true)
    {
        return $this->_updateBunch($entities);
    }

    /**
     * @param array $entity
     * @return bool
     */
    protected function _update(array $entity)
    {
        $arraySource = $this->arraySourceFactory->create(['colNames' => $this->colNames]);
        $arraySource->addEntity($entity);
        return $this->importBunch($arraySource, MagentoImport::BEHAVIOR_ADD_UPDATE);
    }

    /**
     * @param array $entities
     * @return bool
     */
    protected function _updateBunch(array $entities)
    {
        $arraySource = $this->arraySourceFactory->create([
            'colNames' => $this->colNames,
            'data' => $entities,
        ]);
        return $this->importBunch($arraySource, MagentoImport::BEHAVIOR_ADD_UPDATE);
    }

    /**
     * @param ArraySource $arraySource
     * @param string $behaviour
     * @return bool
     */
    protected function importBunch(ArraySource $arraySource, $behaviour)
    {
        $this->errorAggregator->clear();
        $this->setParameter('behavior', $behaviour);
        $this->getImportProcessor()->setParameters($this->importParameters);
        $this->getImportProcessor()->setSource($arraySource);
        $this->getImportProcessor()->validateData();
        if ($this->errorAggregator->getErrorsCount() > 0) {
            $this->_logger->critical(__('Data is not valid.'), [
                'entity' => $this->getName(),
                'behavior' => $behaviour,
                'errors' => $this->errorAggregator->getErrorMessages(),
                'data' => $arraySource->getData()
            ]);
            return false;
        } else {
            $this->getImportProcessor()->importData();
        }
        return true;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return static::NAME;
    }

    /**
     * @return Data
     */
    protected function getImportProcessor()
    {
        if (!($this->importProcessor instanceof Data)) {
            $this->importProcessor = $this->importProcessorFactory->create(
                [
                    'errorAggregator' => $this->errorAggregator,
                ]
            );
        }
        return $this->importProcessor;
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
}
