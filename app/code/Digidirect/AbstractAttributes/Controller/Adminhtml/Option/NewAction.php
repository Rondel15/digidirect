<?php
namespace Digidirect\AbstractAttributes\Controller\Adminhtml\Option;

/**
 * Class NewAction
 * @package Digidirect\AbstractAttributes\Controller\Adminhtml\Option
 */
class NewAction extends \Magento\Backend\App\Action
{
    use BackTrait;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Digidirect\AbstractAttributes\Model\ResourceModel\AbstractAttribute
     */
    protected $_attributeResource;

    /**
     * NewAction constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Digidirect\AbstractAttributes\Model\ResourceModel\AbstractAttribute $resource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Digidirect\AbstractAttributes\Model\ResourceModel\AbstractAttribute $resource
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_attributeResource = $resource;

        parent::__construct($context);
    }

    /**
     * Create new option
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        if (empty($this->_attributeResource->getAvailableAttributes())) {
            $this->messageManager->addErrorMessage(
                __('There are no available attributes, please enable it in attribute settings.')
            );
            return $this->_resultRedirect();
        }
        return $resultForward->forward('edit');
    }
}
