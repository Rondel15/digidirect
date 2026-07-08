<?php
namespace Digidirect\Blog\Block\Post\Comments;

use Digidirect\Blog\Helper\Data;
use Digidirect\Blog\Api\CommentRepositoryInterface;
use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;
use Magento\Framework\Registry;

/**
 * Class DefaultType
 */
class DefaultType extends AbstractCommentType
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CommentRepositoryInterface
     */
    protected $commentRepository;

    /**
     * DefaultType constructor.
     * @param Template\Context $context
     * @param Data $dataHelper
     * @param Session $customerSession
     * @param Registry $registry
     * @param CommentRepositoryInterface $commentRepository
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $dataHelper,
        Session $customerSession,
        Registry $registry,
        CommentRepositoryInterface $commentRepository,
        array $data
    ) {
        parent::__construct($context, $dataHelper, $registry, $data);
        $this->customerSession = $customerSession;
        $this->commentRepository = $commentRepository;
    }

    /**
     * @return bool
     */
    public function isCommentFormAvailable()
    {
        $result = true;
        if ($this->isLoginRequire()) {
            $result = $this->isCustomerLoggedIn();
        }
        return $result;
    }

    /**
     * @return bool
     */
    public function isLoginRequire()
    {
        return (bool)$this->dataHelper->getCommentSettingsConfig('login_require');
    }

    /**
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @return \Digidirect\Blog\Model\ResourceModel\Comment\Collection
     */
    public function getCommentsCollection()
    {
        if (!$this->hasData('comments_collection')) {
            $collection = $this->commentRepository->getCommentsList($this->getPostId());
            $this->setData('comments_collection', $collection);
        }
        return $this->getData('comments_collection');
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('blog/comment/save');
    }

    /**
     * Prepare faq list toolbar
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var \Magento\Theme\Block\Html\Pager $toolbar */
        $toolbar = $this->getLayout()->getBlock('comment_list_pager');
        if ($toolbar) {
            $commentsPerPage = $this->dataHelper->getCommentSettingsConfig('commentcount');
            $toolbar->setShowPerPage(false);
            $toolbar->setLimit($commentsPerPage);
            $collection = $this->getCommentsCollection();
            $toolbar->setCollection($collection);
            $this->setChild('blog_toolbar', $toolbar);
        }
        return $this;
    }
}
