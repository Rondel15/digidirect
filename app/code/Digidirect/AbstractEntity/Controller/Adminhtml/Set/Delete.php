<?php
namespace Digidirect\AbstractEntity\Controller\Adminhtml\Set;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\App\Cache\TypeListInterface;

class Delete extends \Magento\Catalog\Controller\Adminhtml\Product\Set
{
    const ADMIN_RESOURCE = 'Digidirect_AbstractEntity::abstractentity_delete';

    /**
     * @var TypeListInterface
     */
    protected $typeList;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * Delete constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param TypeListInterface $typeList
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        AttributeSetRepositoryInterface $attributeSetRepository,
        TypeListInterface $typeList,
        array $data = []
    ) {
        parent::__construct($context, $coreRegistry);
        $this->typeList = $typeList;
        $this->data = $data;
        $this->attributeSetRepository = $attributeSetRepository;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $setId = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $this->attributeSetRepository->deleteById($setId);
            $this->typeList->invalidate($this->data['invalidate_cache_types'] ?? []);
            $this->messageManager->addSuccessMessage(
                __('The entity has been removed. Please clear invalid cache types.')
            );
            $resultRedirect->setPath('digidirect_abstractentity/*/');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t delete this entity right now.'));
            $resultRedirect->setUrl($this->_redirect->getRedirectUrl($this->getUrl('*')));
        }
        return $resultRedirect;
    }
}
