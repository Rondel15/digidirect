<?php
namespace Digidirect\AbstractAttributes\Controller\Adminhtml\Option;

use Digidirect\AbstractAttributes\Api\Data\OptionInterfaceFactory as OptionFactory;
use Digidirect\AbstractAttributes\Api\OptionRepositoryInterface;
use Digidirect\AbstractAttributes\Model\CacheInvalidator;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var OptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @var OptionFactory
     */
    protected $optionFactory;

    /**
     * @var CacheInvalidator
     */
    protected $cacheInvalidator;

    /**
     * Delete constructor.
     * @param Context $context
     * @param OptionRepositoryInterface $optionRepository
     * @param OptionFactory $optionFactory
     * @param CacheInvalidator $cacheInvalidator
     */
    public function __construct(
        Context $context,
        OptionRepositoryInterface $optionRepository,
        OptionFactory $optionFactory,
        CacheInvalidator $cacheInvalidator = null
    ) {
        $this->optionRepository = $optionRepository;
        $this->optionFactory = $optionFactory;
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
        $id = (int)$this->getRequest()->getParam('option_id');

        try {
            $this->optionRepository->deleteById($id);
            $this->cacheInvalidator->invalidate();
            $this->messageManager->addSuccessMessage(__('Option has been deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

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
