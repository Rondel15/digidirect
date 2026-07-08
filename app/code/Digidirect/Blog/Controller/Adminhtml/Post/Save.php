<?php

namespace Digidirect\Blog\Controller\Adminhtml\Post;

use Digidirect\Blog\Api\PostRepositoryInterface;
use Digidirect\Blog\Controller\Adminhtml\AbstractSaveAction;
use Digidirect\Blog\Model\CacheInvalidator;
use Digidirect\Blog\Model\CurrentStoreFetcher;
use Digidirect\Blog\Model\PostFactory;
use Digidirect\Blog\Model\PostRepository;
use Digidirect\Blog\Model\UrlModel;
use Digidirect\Blog\Sql\PostInformationSave;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Save
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends AbstractSaveAction
{
    /**
     * @var PostRepository
     */
    protected $postRepository;

    /**
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * @var UrlModel
     */
    protected $urlModel;

    /**
     * @var CacheInvalidator
     */
    protected $cacheInvalidator;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param PostRepositoryInterface $postRepository
     * @param PostFactory $postFactory
     * @param UrlModel $urlModel
     * @param CacheInvalidator $cacheInvalidator
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Action\Context $context,
        PostRepositoryInterface $postRepository,
        PostFactory $postFactory,
        UrlModel $urlModel,
        CacheInvalidator $cacheInvalidator,
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
        $this->postRepository = $postRepository;
        $this->postFactory = $postFactory;
        $this->urlModel = $urlModel;
        $this->cacheInvalidator = $cacheInvalidator;
        parent::__construct($context);
    }

    /**
     * Save Menu Item
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getId('post');
        $currentStoreId = $this->getRequest()->getParam(CurrentStoreFetcher::PARAM_STORE);
        $postData = $this->getRequest()->getPostValue();
        if ($postData) {
            try {
                $model = $this->postFactory->create();
                if (!empty($id)) {
                    $model = $this->postRepository->getById($id);
                } else {
                    $postData['entity_id'] = null;
                }
                if (!$model->getId() && $id) {
                    throw new LocalizedException(__('This post no longer exists.'));
                }

                if(!$model->getId()) {
                    $model->setData(PostInformationSave::POST_IS_NEW, true);
                }

                $this->extractPostData($model, $postData);
                $this->urlModel->prepareUrlKey($model, 'title');
                $this->_eventManager->dispatch(
                    'post_prepare_save',
                    ['model' => $model, 'request' => $this->getRequest()]
                );
                $relatedPosts = null;
                if (!empty($postData['in_related_post'])) {
                    $relatedPosts = $this->serializer->unserialize($postData['in_related_post']);
                }

                $relatedProducts = null;
                if (!empty($postData['in_related_products'])) {
                    $relatedProducts = $this->serializer->unserialize($postData['in_related_products']);
                }
                $this->postRepository->save($model, $relatedPosts, $relatedProducts);

                $this->messageManager->addSuccessMessage(__('You saved post'));

                $this->cacheInvalidator->invalidate();

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [
                        'id' => $model->getId(),
                        CurrentStoreFetcher::PARAM_STORE => $currentStoreId,
                    ]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
            }

            return $resultRedirect->setPath('*/*/edit', [
                'id' => $id,
                CurrentStoreFetcher::PARAM_STORE => $currentStoreId
            ]);
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check permissions for this action
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Digidirect_Blog::blogpost');
    }
}
