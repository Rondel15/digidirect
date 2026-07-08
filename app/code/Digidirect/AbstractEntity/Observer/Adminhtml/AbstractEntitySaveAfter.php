<?php
namespace Digidirect\AbstractEntity\Observer\Adminhtml;

use Magento\Framework\Event\ObserverInterface;
use Digidirect\AbstractEntity\Model\AbstractEntity;
use Magento\Eav\Model\Entity\TypeFactory;
use \Magento\Framework\App\RequestInterface;
use Digidirect\AbstractEntity\Model\AttributeSetRepository;

/**
 * Class AbstractEntitySaveAfter
 * @package Digidirect\AbstractEntity\Observer\Adminhtml
 */
class AbstractEntitySaveAfter implements ObserverInterface
{
    /**
     * @var TypeFactory
     */
    protected $_eavTypeFactory;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var AttributeSetRepository
     */
    protected $_repository;

    /**
     * AbstractEntitySaveAfter constructor.
     * @param TypeFactory $typeFactory
     * @param RequestInterface $request
     * @param AttributeSetRepository $setRepository
     */
    public function __construct(
        TypeFactory $typeFactory,
        RequestInterface $request,
        AttributeSetRepository $setRepository
    ) {
        $this->_eavTypeFactory = $typeFactory;
        $this->_request = $request;
        $this->_repository = $setRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $model = $observer->getEvent()->getDataObject();
        $entityType = $this->_eavTypeFactory->create()->loadByCode(AbstractEntity::ENTITY_TYPE);
        if ($entityType->getEntityTypeId() == $model->getEntityTypeId()) {
            $data = array_merge(
                $model->getData(),
                $this->_request->getParams(),
                $model->getOrganizedData() ?: []
            );
            $this->_repository->processRelations($model->getAttributeSetId(), $data);
        }
        return $this;
    }
}
