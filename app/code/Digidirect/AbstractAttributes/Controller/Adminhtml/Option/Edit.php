<?php
namespace Digidirect\AbstractAttributes\Controller\Adminhtml\Option;

use Digidirect\AbstractAttributes\Model\ResourceModel\Option as ResourceOption;

/**
 * Class Edit
 * @package Digidirect\AbstractAttributes\Controller\Adminhtml\Option
 */
class Edit extends \Magento\Backend\App\Action
{
    use BackTrait;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ResourceOption
     */
    protected $_resourceOption;

    /**
     * Edit constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param ResourceOption $resourceOption
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        ResourceOption $resourceOption
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_resourceOption = $resourceOption;

        parent::__construct($context);
    }

    /**
     * Edit option
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('option_id');
        $attributeId = $this->_resourceOption->getAttributeId($id);
        if ($id && !($attributeId && $this->_resourceOption->isOptionAvailable($attributeId))) {
            $this->messageManager->addErrorMessage(__('This option is not available.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            return $this->_resultRedirect();
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Option'));
        $resultPage->getConfig()->getTitle()->prepend($id ? __('Edit') : __('New'));
        if (!$id) {
            $resultPage->getLayout()->unsetElement('store_switcher');
        }

        return $resultPage;
    }
}
