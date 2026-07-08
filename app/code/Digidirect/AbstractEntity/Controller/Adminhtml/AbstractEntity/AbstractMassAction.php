<?php
namespace Digidirect\AbstractEntity\Controller\Adminhtml\AbstractEntity;

use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Controller\Adminhtml\AbstractEntity as AbstractEntityController;
use Digidirect\AbstractEntity\Api\AbstractEntityRepositoryInterface;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity\CollectionFactory;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Registry;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Store\Model\Store;

abstract class AbstractMassAction extends AbstractEntityController
{
    const INDEX_ACTION = 'digidirect_abstractentity/*/index';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param AbstractEntityRepositoryInterface $abstractEntityRepository
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        AbstractEntityRepositoryInterface $abstractEntityRepository,
        AttributeSetRepositoryInterface $attributeSetRepository,
        CollectionFactory $collectionFactory,
        Filter $filter
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        parent::__construct(
            $context,
            $coreRegistry,
            $resultPageFactory,
            $resultForwardFactory,
            $abstractEntityRepository,
            $attributeSetRepository
        );
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            return $this->massAction($collection);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath(static::INDEX_ACTION, [
                AbstractEntityInterface::ATTRIBUTE_SET_ID => $this->_initAttributeSet()->getAttributeSetId()
            ]);
        }
    }

    /**
     * Process mass status change
     *
     * @param AbstractDb $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    abstract protected function massAction(AbstractDb $collection);
}
