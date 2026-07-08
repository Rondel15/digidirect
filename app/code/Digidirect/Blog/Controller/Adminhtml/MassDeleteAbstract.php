<?php

namespace Digidirect\Blog\Controller\Adminhtml;

use Digidirect\Blog\Model\CacheInvalidator;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Digidirect\Blog\Api\AbstractRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDeleteAbstract
 */
class MassDeleteAbstract extends AbstractMassAction
{
    /**
     * @var AbstractRepositoryInterface
     */
    protected $repository;

    /**
     * MassDeleteAbstract constructor.
     * @param Context $context
     * @param Filter $filter
     * @param AbstractRepositoryInterface $repository
     * @param CacheInvalidator|null $cacheInvalidator
     */
    public function __construct(
        Context $context,
        Filter $filter,
        AbstractRepositoryInterface $repository,
        CacheInvalidator $cacheInvalidator = null
    ) {
        parent::__construct($context, $filter);
        $this->repository = $repository;
    }

    /**
     * Delete record using repository
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $recordsDeleted = 0;
        foreach ($collection->getAllIds() as $itemId) {
            $this->repository->deleteById($itemId);
            $recordsDeleted++;
        }

        if ($recordsDeleted) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were deleted.', $recordsDeleted));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());
        $this->cacheInvalidator->invalidate();

        return $resultRedirect;
    }
}
