<?php
namespace Digidirect\AbstractAttributes\Controller\Attribute;

use Digidirect\AbstractAttributes\Api\AbstractAttributeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Index
 * @package Digidirect\AbstractAttributes\Controller\Listing
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var AbstractAttributeRepositoryInterface
     */
    protected $abstractAttributeRepository;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param AbstractAttributeRepositoryInterface $abstractAttributeRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        AbstractAttributeRepositoryInterface $abstractAttributeRepository
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
        $this->abstractAttributeRepository = $abstractAttributeRepository;
        parent::__construct($context);
    }

    /**
     * @return bool|\Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface
     */
    protected function _initAbstractAttribute()
    {
        $attributeId = (int)$this->getRequest()->getParam('_aa');
        try {
            $attribute = $this->abstractAttributeRepository->getByAttributeId(
                $attributeId,
                $this->storeManager->getStore()->getId()
            );
        } catch (NoSuchEntityException $e) {
            return false;
        }

        if (!$attribute->getListingEnabled()) {
            return false;
        }

        $this->coreRegistry->register('current_eaa', $attribute);
        return $attribute;
    }

    /**
     * View CMS page action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $attribute = $this->_initAbstractAttribute();
        if ($attribute) {
            $resultPage = $this->resultPageFactory->create();
            $this->_eventManager->dispatch(
                'controller_action_layout_render_before_eaa_view',
                ['eaa' => $attribute]
            );
            return $resultPage;
        } else {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
    }
}
