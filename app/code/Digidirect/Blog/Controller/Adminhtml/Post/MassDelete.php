<?php
namespace Digidirect\Blog\Controller\Adminhtml\Post;

use Digidirect\Blog\Api\PostRepositoryInterface;
use Digidirect\Blog\Controller\Adminhtml\MassDeleteAbstract;
use Digidirect\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassDelete
 */
class MassDelete extends MassDeleteAbstract
{
    /**
     * @var PostRepositoryInterface
     */
    protected $repository;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param Filter $filter
     * @param PostRepositoryInterface $repository
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        PostRepositoryInterface $repository,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context, $filter, $repository);
        $this->collectionFactory = $collectionFactory;
    }
}
