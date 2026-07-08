<?php
namespace Digidirect\Blog\Controller\Adminhtml\Comment;

use Digidirect\Blog\Api\CommentRepositoryInterface;
use Digidirect\Blog\Controller\Adminhtml\MassDeleteAbstract;
use Digidirect\Blog\Model\ResourceModel\Comment\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassDelete
 */
class MassDelete extends MassDeleteAbstract
{
    /**
     * @var CommentRepositoryInterface
     */
    protected $repository;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CommentRepositoryInterface $repository
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CommentRepositoryInterface $repository,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context, $filter, $repository);
        $this->collectionFactory = $collectionFactory;
    }
}
