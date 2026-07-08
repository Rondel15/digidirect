<?php

namespace Digidirect\Feed\Controller\Adminhtml\Feed;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Digidirect\Feed\Controller\Adminhtml\Feed;
use Digidirect\Feed\Model\FeedFactory;
use Digidirect\Feed\Model\FeedRepository;
use Digidirect\Feed\Model\TemplateFactory;

class Save extends Feed
{
    /**
     * @var TemplateFactory
     */
    protected $templateFactory;

    /**
     * Save constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FeedFactory $feedFactory
     * @param FeedRepository $feedRepository
     * @param TemplateFactory $templateFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FeedFactory $feedFactory,
        FeedRepository $feedRepository,
        TemplateFactory $templateFactory
    ) {
        $this->templateFactory = $templateFactory;

        parent::__construct($context, $registry, $feedFactory, $feedRepository);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            try {
                $model = $this->initModel();

                if (!$model->getId() && $id) {
                    $this->messageManager->addErrorMessage(__('This feed no longer exists.'));

                    return $resultRedirect->setPath('*/*/');
                }

                $data = $this->filterPostData($data);

                if (isset($data['template_id'])) {
                    $template = $this->templateFactory->create();
                    $template->getResource()->load($template, $data['template_id']);
                    $model->loadFromTemplate($template);
                }

                $model->addData($data);
                
                $this->feedRepository->save($model);

                $this->messageManager->addSuccessMessage(__('You saved the feed.'));
                $this->backendSession->setFormData(false);

                if ($this->getRequest()->getParam('back') == 'edit') {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }

                return $this->context->getResultRedirectFactory()->create()->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong while trying to save the feed.'));
                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect;
        }
    }

    /**
     * Filter post data
     *
     * @param array $data
     * @return array
     */
    public function filterPostData($data)
    {
        $feed = $data['feed'];
        unset($data['feed']);

        $feed['rule_ids'] = isset($feed['rule_ids']) ? $feed['rule_ids'] : [];
        $feed['rule_ids'] = array_keys($feed['rule_ids']);

        $data = array_merge($data, $feed);

        return $data;
    }
}
