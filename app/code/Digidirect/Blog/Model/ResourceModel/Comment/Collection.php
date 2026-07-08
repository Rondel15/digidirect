<?php
namespace Digidirect\Blog\Model\ResourceModel\Comment;

use Digidirect\Blog\Api\Data\PostInterface;
use Digidirect\Blog\Model\CurrentStoreFetcher;
use Digidirect\Blog\Sql\PostInformationJoin;
use Magento\Framework\App\ObjectManager;

/**
 * Class Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var int
     */
    protected $_storeViewId;

    /**
     * @var array
     */
    protected $_addedTable = [];

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_readConnection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CurrentStoreFetcher
     */
    protected $storeFetcher;

    /**
     * @var PostInformationJoin
     */
    protected $postJoin;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param null $connection
     * @param CurrentStoreFetcher|null $currentStoreFetcher
     * @param PostInformationJoin|null $postJoin
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connection = null,
        CurrentStoreFetcher $currentStoreFetcher = null,
        PostInformationJoin $postJoin = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection);
        $this->_storeManager = $storeManager;
        $this->storeFetcher = $currentStoreFetcher ?: ObjectManager::getInstance()->get(CurrentStoreFetcher::class);
        $this->postJoin = $postJoin ?: ObjectManager::getInstance()->get(PostInformationJoin::class);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Digidirect\Blog\Model\Comment', 'Digidirect\Blog\Model\ResourceModel\Comment');
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeLoad()
    {
        $this->addPostDataToSelect();
        return parent::_beforeLoad();
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function addPostDataToSelect()
    {
        $storeId = $this->storeFetcher->getCurrentStoreId();
        $this->getSelect()->joinLeft(
            ['p' => $this->getTable(PostInterface::DIGIDIRECT_BLOG_POST_TABLE)],
            'main_table.post_id = p.entity_id',
            []
        );

        $this->postJoin->join(
            $this->getSelect(),
            $storeId,
            'p'
        );
        return $this;
    }
}
