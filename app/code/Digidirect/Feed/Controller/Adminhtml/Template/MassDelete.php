<?php

namespace Digidirect\Feed\Controller\Adminhtml\Template;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Registry;
use Digidirect\Feed\Model\TemplateFactory;
use Digidirect\Feed\Model\TemplateRepository;
use Digidirect\Feed\Model\ResourceModel\Template as ResourceModel;
use Digidirect\Feed\Controller\Adminhtml\Template;

class MassDelete extends Template
{
    /**
     * @var ResourceModel
     */
    protected $resourceModel;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ForwardFactory $resultForwardFactory
     * @param TemplateFactory $templateFactory
     * @param TemplateRepository $templateRepository
     * @param ResourceModel $resourceModel
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        TemplateFactory $templateFactory,
        TemplateRepository $templateRepository,
        ResourceModel $resourceModel
    ) {
        $this->resourceModel = $resourceModel;

        parent::__construct($context, $registry, $resultForwardFactory, $templateFactory, $templateRepository);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $templateIds = $this->getRequest()->getParam('template', []);
            foreach ($templateIds as $templateId) {
                $this->templateRepository->deleteById($templateId);
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', count($templateIds))
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while trying to delete the templates.'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
