<?php
namespace Digidirect\AbstractAttributes\Controller\Adminhtml\Option;

use Digidirect\AbstractAttributes\Api\OptionRepositoryInterface;
use Digidirect\AbstractAttributes\Api\Data\OptionInterface;
use Digidirect\AbstractAttributes\Model\CacheInvalidator;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassDelete
 * @package Digidirect\AbstractAttributes\Controller\Adminhtml\Option
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var OptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CacheInvalidator
     */
    protected $cacheInvalidator;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param OptionRepositoryInterface $optionRepository
     * @param Filter $filter
     * @param CacheInvalidator $cacheInvalidator
     */
    public function __construct(
        Context $context,
        OptionRepositoryInterface $optionRepository,
        Filter $filter,
        CacheInvalidator $cacheInvalidator = null
    ) {
        $this->optionRepository = $optionRepository;
        $this->filter = $filter;
        $this->cacheInvalidator = $cacheInvalidator ?: ObjectManager::getInstance()->get(CacheInvalidator::class);
        parent::__construct($context);
    }

    /**
     * Mass delete options
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->optionRepository->getCollection());
        $items = $collection->getItems();

        $counter = 0;
        foreach ($items as $item) {
            try {
                /** @var OptionInterface $item */
                $this->optionRepository->delete($item);
                $counter++;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        if ($counter > 0) {
            $this->cacheInvalidator->invalidate();
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been deleted.', $counter)
        );

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check if Is allowed to delete
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Digidirect_AbstractAttributes::aa_menu_option_delete');
    }
}
