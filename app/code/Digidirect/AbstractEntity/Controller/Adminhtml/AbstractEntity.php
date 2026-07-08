<?php
namespace Digidirect\AbstractEntity\Controller\Adminhtml;

use Digidirect\AbstractEntity\Api\AbstractEntityRepositoryInterface;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Model\Registry\Constants;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Registry;

abstract class AbstractEntity extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE_PREFIX = 'Digidirect_AbstractEntity::abstractentity_record_view_';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var AbstractEntityRepositoryInterface
     */
    protected $abstractEntityRepository;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param AbstractEntityRepositoryInterface $abstractEntityRepository
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        AbstractEntityRepositoryInterface $abstractEntityRepository,
        AttributeSetRepositoryInterface $attributeSetRepository
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->abstractEntityRepository = $abstractEntityRepository;
        $this->attributeSetRepository = $attributeSetRepository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Eav\Api\Data\AttributeSetInterface
     */
    protected function _initAttributeSet()
    {
        if (!$attributeSet = $this->_coreRegistry->registry(Constants::CURRENT_ATTRIBUTE_SET)) {
            $setId = (int)$this->getRequest()->getParam(AbstractEntityInterface::ATTRIBUTE_SET_ID);
            $attributeSet = $this->attributeSetRepository->get($setId);
            $this->_coreRegistry->register(Constants::CURRENT_ATTRIBUTE_SET, $attributeSet);
        }
        return $attributeSet;
    }

    /**
     * @return string
     */
    protected function getAdminResourcePath()
    {
        $attributeSet = $this->_initAttributeSet();
        return static::ADMIN_RESOURCE_PREFIX . $attributeSet->getAttributeSetId();
    }

    /**
     * Check if Is allowed to view
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed($this->getAdminResourcePath());
    }

    /**
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return mixed
     */
    public function initPage($resultPage)
    {
        $resultPage->setActiveMenu($this->getAdminResourcePath())
            ->addBreadcrumb(__('Digidirect'), __('Digidirect'))
            ->addBreadcrumb(__('Abstract Entity'), __('Abstract Entity'));
        return $resultPage;
    }
}
