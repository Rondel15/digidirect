<?php
namespace Digidirect\AbstractAttributes\Controller\Adminhtml\Option;

use Digidirect\AbstractAttributes\Api\Data\OptionInterfaceFactory;
use Digidirect\AbstractAttributes\Api\OptionRepositoryInterface;
use Digidirect\AbstractAttributes\Helper\Image as ImageHelper;
use Digidirect\AbstractAttributes\Model\ResourceModel\Option as ResourceOption;
use Digidirect\AbstractAttributes\Model\CacheInvalidator;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\UrlRewrite\Model\UrlRewriteFactory;

/**
 * Class Save
 * @package Digidirect\AbstractAttributes\Controller\Adminhtml\Option
 */
class Save extends \Magento\Backend\App\Action
{
    use BackTrait;

    /**
     * @var OptionInterfaceFactory
     */
    protected $_optionFactory;

    /**
     * @var OptionRepositoryInterface
     */
    protected $_optionRepository;

    /**
     * @var CacheInvalidator
     */
    protected $_cacheInvalidator;

    /**
     * Save constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param OptionInterfaceFactory $optionFactory
     * @param OptionRepositoryInterface $optionRepository
     * @param CacheInvalidator $cacheInvalidator
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        OptionInterfaceFactory $optionFactory,
        OptionRepositoryInterface $optionRepository,
        CacheInvalidator $cacheInvalidator = null
    ) {
        $this->_optionFactory = $optionFactory;
        $this->_optionRepository = $optionRepository;
        $this->_cacheInvalidator = $cacheInvalidator ?: ObjectManager::getInstance()->get(CacheInvalidator::class);

        parent::__construct($context);
    }

    /**
     * Save action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            $hasError = false;
            /** @var \Digidirect\AbstractAttributes\Model\Option $option */
            $option = $this->_optionFactory->create($data);
            $option->setData($data);

            try {
                $this->_optionRepository->save($option);
                $this->_cacheInvalidator->invalidate();
                $this->messageManager->addSuccessMessage(__('You saved Advanced Option'));
            } catch (\Exception $e) {
                $hasError = true;
                $this->messageManager->addErrorMessage($e->getMessage());
            }

            $id = $option->getOptionId();
            if (($hasError || $this->getRequest()->getParam('back')) && $id) {
                $params = [
                    'option_id' => $id,
                    '_current'  => true,
                    'store'     => $this->getRequest()->getParam('store_id'),
                ];
                if ($backId = $this->getRequest()->getParam('back_to_edit_attribute_id')) {
                    $params['attribute_id'] = $backId;
                }
                return $this->resultRedirectFactory->create()->setPath('*/*/edit', $params);
            }

            if ($hasError) {
                $this->_getSession()->setEaaOptionData($data);
                return $this->resultRedirectFactory->create()->setPath('*/*/edit');
            }
        }

        return $this->_resultRedirect();
    }

    /**
     * Check if Is allowed to save
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Digidirect_AbstractAttributes::aa_menu_option_save');
    }
}
